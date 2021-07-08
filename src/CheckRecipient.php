<?php

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A postfix "policy service" that checks whether a recipient address is valid.
 *
 * http://www.postfix.org/SMTPD_POLICY_README.html
 *
 * The Postfix master process will spawn this as a background daemon and connect stdin/stdout to a TCP/IP socket.
 * Whenever it receives an email postfix will send details to this process over that socket and we should reply with
 * one of the following:
 *
 *       "DUNNO"                  this policy server accepts the mail, "DUNNO" tells postfix to continue with the rest of the configured restriction list
 *       "PREPEND Header: value"  as above, but also adds a header to the mail
 *       "DEFER_IF_PERMIT"        defers the message after checking other restrictions, use this for temporary failures (however, if another restriction in the list would reject the mail, then it's rejected)
 *       "REJECT message"         rejects the message with a 521 error
 *
 * see http://www.postfix.org/access.5.html
 *
 */
class Comms_Scripts_CheckRecipient extends Core_Scripts_AbstractBaseScript
{
	/**
	 * Set to true if we receive a SIGHUP to tell the main loop to exit after it's processed the current email.
	 * @var boolean
	 */
	private $bExitOnNextLoop = false;

	/**
	 * Stores the script's mtime. We check at the end of each loop to see if we need a restart.
	 * @var integer
	 */
	private $iScriptMtime;

	/**
	 * Logger object in use
	 * @var \Psr\Log\LogInterface
	 */
	private $oLog;


	/**
	 * @param \Psr\Log\LogInterface $oLog the logger to use, uses the default if none provided
	 */
	public function __construct(\Psr\Log\LogInterface $oLog = null)
	{
		global $goManager;

		parent::__construct();

		$this->oLog = $oLog ?: $goManager->GetLogger();
	}

	public function GetCommandName()
	{
		return 'Comms:CheckRecipient';
	}

	public function GetDescriptionForCommand()
	{
		return 'postfix policy service that checks whether an email can be accepted based on its recipient';
	}

	/**
	 * Main loop for the policy service: Receive a bunch of attributes, evaluate the policy, send the result.
	 *
	 * Basically a PHP port of the greylist.pl script in the sysadmin "MXes" repository.
	 */
	protected function PerformScriptAction()
	{
		pcntl_signal(SIGHUP, function()
		{
			$this->oLog->info('SIGHUP received; gracefully shutting down after the next iteration.');
			$this->bExitOnNextLoop = true;
		});

		$rStdIn = fopen('php://stdin', 'r');
		$aEmailAttribs = [];
		declare(ticks = 1)
		{
			while ($sLine = fgets($rStdIn)) 	// assignment
			{
				if (preg_match('/^([^=]+)=(.*)$/', trim($sLine), $aMatches))
				{
					$aEmailAttribs[$aMatches[1]] = $aMatches[2];
				}
				elseif ($sLine === "\n")
				{
					if (isset($aEmailAttribs['recipient']))
					{
						cli_set_process_title('Comms:CheckRecipient - checking ' . $aEmailAttribs['queue_id'] . ' - ' . $aEmailAttribs['recipient']);

						$sAction = $this->CheckRecipientIsValid($aEmailAttribs['recipient']);
						print "action=$sAction\n\n";
					}
					else
					{
						$this->oLog->warning('No recipient attrib received from Postfix', ['EmailAttribs' => $aEmailAttribs]);
					}

					$aEmailAttribs = [];
					cli_set_process_title('Comms:CheckRecipient - idle');

					if ($this->bExitOnNextLoop)
					{
						$this->oLog->info('Exiting due to SIGHUP');
						return EXIT_OK;
					}
				}
				else
				{
					$this->oLog->info('Ignoring unknown input from Postfix: ' . $sLine);
				}
			}
		}
	}

	private function CheckRecipientIsValid($sRecipient)
	{
		global $goManager;

		if (stripos($sRecipient, COMMS_EMAIL_REPLY_PREFIX) !== 0)
		{
			// This is not a comms reply, so ignore it
			return 'DUNNO';
		}

		$oProcessEmail = new Comms_ProcessEmail();
		$sBounceText = '';
		$iExitStatus = $oProcessEmail->IsRecipientValid($sRecipient, $sBounceText);

		switch($iExitStatus)
		{
			case EXIT_OK:
				return 'DUNNO';
			case EXIT_DEFERMAIL:
				return "DEFER_IF_PERMIT $sBounceText";
			case EXIT_BOUNCEMAIL:
				return "REJECT $sBounceText";

			default:
				trigger_error("Unknown status from IsRecipientValid: $iExitStatus", E_USER_ERROR);
		}
	}
}
