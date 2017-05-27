#!/usr/bin/env python

import cgi
import requests
import sys, os, syslog
from flup.server.fcgi import WSGIServer
from urlparse import parse_qs
from multiprocessing import Process

import clip2


def get_pops(geom, callback_url):
    pops = clip2.count_all_populations(geom, '/var/www/html/data')

    r = requests.post(callback_url, {'pops': pops})

    syslog.syslog("URL: " + callback_url)
    syslog.syslog("status: " + str(r.status_code))
    syslog.syslog("content: " + r.text)


def app(environ, start_response):

    if environ['REQUEST_METHOD'] == 'POST':
        post_env = environ.copy()
        post_env['QUERY_STRING'] = ''
        post = cgi.FieldStorage(
            fp=environ['wsgi.input'],
            environ=post_env,
            keep_blank_values=True
        )

    callback_url = post['callback'].value
    geom = post['geom'].value

    # start process in background
    syslog.syslog("starting background process")
    p = Process(target=get_pops, args=(geom,callback_url))
    p.start()

    start_response('200 OK', [('Content-Type', 'text/plain')])
    yield "process started in the background\n"


WSGIServer(app).run()
