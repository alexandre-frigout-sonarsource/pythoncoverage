import unittest
from src.main import getarguments, conditional, newcodecovNewtest, Newtest, Mynewtest, tocoverinnewcode


class TestArgumentsList(unittest.TestCase):
    def setUp(self):
        self.myargs = getarguments('coco', 'toto')
        self.mycond = conditional(5)
        self.myc = newcodecovNewtest(4)
        self.mc = newcodecovNewtest(1)
        self.mn = Newtest(1)
        self.mnt = Mynewtest(1)
        self.y = tocoverinnewcode(5)

    def test_type(self):
        self.assertEqual(isinstance(self.myargs, list), True)

    def test_conditional(self):
        self.assertEqual(self.mycond, 'Less than 10')

    def test_my(self):
        self.assertEqual(self.myc, "ko")

    def test_mc(self):
        self.assertEqual(self.mc, "ok")

    def test_mn(self):
        self.assertEqual(self.mn, "ok")

    def test_mnt(self):
        self.assertEqual(self.mnt, True)

    def test_y(self):
        self.assertEqual(self.y, ">4")

if __name__ == '___':
        unittest.main()
