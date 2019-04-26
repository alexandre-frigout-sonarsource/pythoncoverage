import os, sys

def myargs(*args):
    l = [it for it in args]
    if len(l) > 1:
        return len(l)
    else:
        return "No arguments provided"

def brfc(*args):
    o = [it for it in args]
    f = "ok" in o
    if f == True:
        return f
    elif f == False:
        return f
    else:
        return "not good...."
