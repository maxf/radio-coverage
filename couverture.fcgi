#!/usr/bin/env python

import cgi
import requests
import sys, os
import json
from flup.server.fcgi import WSGIServer
from urlparse import parse_qs
from multiprocessing import Process
from datetime import datetime

import clip


def log(logfile, message):
    logfile.write(str(message) + '\n')
    logfile.flush()


def get_pops(logfile, radio_data, config, callback_url):
    log(logfile, "<pre>starting background process...\n")
    result = {}
    try:
        result['coverage'] = clip.count_all_populations(
            radio_data['geojson'],
            config['html_path'],
            config['data_files'],
            logfile
        )
    except Exception as e:
        log(logfile, "except:")
        log(str(e))

    result['name'] = radio_data['name']
    result_json = json.dumps(result)

    log(logfile, "</pre>")
    log(logfile, "<h1>Computation completed. Result: </h1>")
    log(logfile, "<pre>%s</pre>" % result_json)

    r = requests.post(callback_url, result_json)

    log(logfile, "<h2>Callback</h2>")
    log(logfile, "<p>Posting result to %s</p>" % callback_url)
    log(logfile, "<p>Server response status: %d</p>" % r.status_code)
    log(logfile, "<p>Server response content: <pre>%s</pre></p>" % r.text)
    log(logfile, "<p>finished.</p>")
    log(logfile, "<p><a href='/'>Go back</a></p>")
    log(logfile, "<script>clearInterval(z)</script>")
    log(logfile, "</body>")
    log(logfile, "</html>")
    logfile.close()


def app(environ, start_response):

    logfilename = "couverture-%s.log" % datetime.now().isoformat()
    logfilepath = "/var/www/html/jobs/" + logfilename
    logfile = open(logfilepath, "w")

    if environ['REQUEST_METHOD'] == 'POST':

        with open("config.json") as config_file:
            config = json.load(config_file)

        post_env = environ.copy()
        post_env['QUERY_STRING'] = ''
        post = cgi.FieldStorage(
            fp=environ['wsgi.input'],
            environ=post_env,
            keep_blank_values=True
        )

        try:
            callback_url = post['callback'].value
            radiojson = post['geom'].value
        except Exception:
            start_response('400 Bad Request', [('Content-Type', 'text/plain')])
            yield "You must pass two POST parameters: callback and geo\n"
        try:
            radio_data = json.loads(radiojson)
        except Exception:
            start_response('400 Bad Request', [('Content-Type', 'text/plain')])
            yield "Invalid JSON passed\n"


        log(logfile, '''<html><body><h1>Radio Population Coverage</h1>
        <script>var z = setInterval(function() { window.location.reload(true) }, 3000);</script>''')

        p = Process(target=get_pops, args=(logfile, radio_data, config, callback_url))
        p.start()

        start_response('303 See Other', [('Location', '/jobs/%s' % logfilename)])
        yield "process started in the background."
    else:
        start_response('400 Bad Request', [('Content-Type', 'text/plain')])
        yield "Please use POST for this URL\n"

WSGIServer(app).run()
