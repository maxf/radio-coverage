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

logfilename = "couverture-%s.log" % datetime.now().isoformat()
logfilepath = "/var/www/html/logs/" + logfilename
logfile = open(logfilepath, "w")
def log(message):
    logfile.write(str(message) + '\n')
    logfile.flush()

log('''<html><head><meta http-equiv="refresh" content="3"></head><body><pre>''')


def get_pops(geom, callback_url):
    pops = clip.count_all_populations(geom, '/var/www/html/data', logfile)
    popsj = json.dumps(pops)

    log("</pre>")
    log("<h1>Computation completed. Result: </h1>")
    log("<pre>%s</pre>" % popsj)

    r = requests.post(callback_url, {'populations': popsj})

    log("<h2>Callback</h2>")
    log("<p>Posting result to %s</p>" % callback_url)
    log("<p>Server response status: %d</p>" % str(r.status_code))
    log("<p>Server response content: %s</p>" % r.text)
    log("<p>finished.</p>")
    log("</body>")
    log("</html>")
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

        start_response('303 See Other', [('Location', '/logs/%s' % logfilename)])
        yield "process started in the background. Log at: http://<server>/logs/%s\n" % logfilename
    else:
        start_response('400 Bad Request', [('Content-Type', 'text/plain')])
        yield "Please use POST for this URL\n"

WSGIServer(app).run()
