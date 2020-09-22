import os, sys, subprocess


PASSWORD = '123456'

def getarguments(*args):
    lists = [item for item in args]
    params = ["ls", "-l"]
    subprocess.run(params)
    subprocess.Popen(params)
    return lists

def conditional(testvalue):
    if testvalue < 10:
        return "Less than 10"
    else:
        return "More than 10"

def newcodecovtest(mycov):
    if mycov < 2:
        return "ok"
    else:
        return "ko"

def newcodecovNewtest(mycov):
    if mycov < 2:
        return "ok"
    else:
        return "ko"
def RnewcodecovNewtest(mycov):
    if mycov < 2:
        return "ok"
    else:
        return "ko"

def Mytest4():
    return True

def Mytest5():
    return True

def Mynewtest(x):
    if x > 0:
        return True
    if x > 2:
        return False

def Newtest(mycov):
    if mycov < 2:
        return "ok"

def SCMtest():
    return True

def iMySCMtest():
    return True


def tocoverinnewcode(t):
    if t < 4:
        print(">4:")
    else:
        print("<4")

def Open1Issue():
    return 1

def Open2Issue():
    return 1

def funcPR():
    return 4;
