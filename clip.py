import rasterio
from rasterio.tools.mask import mask
import json
import sys
import numpy as np
import syslog

def count_population(src, geoms):
    out_image, out_transform = mask(src, geoms['geometries'], crop=True)

    total = 0
    z = out_image[0]
    total = 0.0
    for band in z:
        for pixel in band:
            if pixel > 0:
                total += pixel
    return int(total)


def count_population_file(filename, geoms):
    with rasterio.open(filename) as src:
        return count_population(src, geoms)


def count_all_populations(geometry, base_path, data_files, output = None):
    syslog.syslog(syslog.LOG_ERR, "ook")
    total_population = {}
    try:
        for file in sorted(data_files):
            if output:
                output.write("processing %s\n" % file['name'])
                output.flush()
            full_path = base_path + '/' + file['name']
            pop = count_population_file(full_path, geometry)
            if output:
                output.write("%s: %d\n" % (file['label'], pop))
                output.flush()
            total_population[file['label']] = str(pop)
    except Exception as e:
        syslog.syslog(syslog.LOG_ERR, "Exception")
        syslog.syslog(syslog.LOG_ERR, str(e))

        total_population['error'] = str(e)

    return total_population


if __name__ == "__main__":
    geoms = ''.join(sys.stdin.readlines())
    with open("config.json") as config_file:
        config = json.load(config_file)

    print(
        count_all_populations(
            json.loads(geoms),
            config['html_path'],
            config['data_files']
        )
    )
