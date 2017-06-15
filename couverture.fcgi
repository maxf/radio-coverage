#!/usr/bin/env python

import cgi
import requests
import sys, os, syslog
import json
from flup.server.fcgi import WSGIServer
from urlparse import parse_qs
from multiprocessing import Process
from datetime import datetime

import clip

logfilename = "/tmp/couverture-%s.log" % datetime.now().isoformat()
logfile = open(logfilename, "w")
def log(message):
    logfile.write(str(message) + '\n')
    logfile.flush()

log("test.fcgi starting")


def get_pops(geom, callback_url):
    pops = clip.count_all_populations(geom, '/var/www/html/data')
    popsj = json.dumps(pops)

    log("Computation completed. Result: ")
    log(popsj)

    r = requests.post(callback_url, {'populations': popsj})

    log("Posting to " + callback_url)
    log("response status: " + str(r.status_code))
    log("response content: " + r.text)
    logfile.close()


def app(environ, start_response):

    if environ['REQUEST_METHOD'] == 'POST':
        post_env = environ.copy()
        post_env['QUERY_STRING'] = ''
        post = cgi.FieldStorage(
            fp=environ['wsgi.input'],
            environ=post_env,
            keep_blank_values=True
        )

        try:
            callback_url = post['callback'].value
            geom = post['geom'].value
        except Exception:
            start_response('400 Bad Request', [('Content-Type', 'text/plain')])
            yield "You must pass two POST parameters: callback and geo\n"


        # start process in background
        log("starting background process. Be patient, it can take a few minutes...")
        p = Process(target=get_pops, args=(geom,callback_url))
        p.start()

        start_response('200 OK', [('Content-Type', 'text/plain')])
        yield "process started in the background. Server logfile is: %s\n" % logfile.name
    else:
        start_response('400 Bad Request', [('Content-Type', 'text/plain')])
        yield "Please use POST for this URL\n"

WSGIServer(app).run()
