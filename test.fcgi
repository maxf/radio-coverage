#!/usr/bin/env python

import cgi
import requests
import sys, os, syslog
import json
from flup.server.fcgi import WSGIServer
from urlparse import parse_qs
from multiprocessing import Process

import clip2

syslog.syslog("test.fcgi starting")


def get_pops(geom, callback_url):
    pops = clip2.count_all_populations(geom, '/var/www/html/data')
    popsj = json.dumps(pops)

    syslog.syslog("Computation completed. Result: ")
    syslog.syslog(popsj)

    r = requests.post(callback_url, {'populations': popsj})

    syslog.syslog("Posting to " + callback_url)
    syslog.syslog("response status: " + str(r.status_code))
    syslog.syslog("response content: " + r.text)


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
        syslog.syslog("starting background process")
        p = Process(target=get_pops, args=(geom,callback_url))
        p.start()

        start_response('200 OK', [('Content-Type', 'text/plain')])
        yield "process started in the background\n"
    else:
        start_response('400 Bad Request', [('Content-Type', 'text/plain')])
        yield "Please use POST for this URL\n"

WSGIServer(app).run()
