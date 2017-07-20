import rasterio
from rasterio.tools.mask import mask
import json
import sys
import numpy as np

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

data_files = [
    { 'name': "SEN14_A0005_adjv1.TIF", 'label': " 0 -  5 ans" },
    { 'name': "SEN14_A0510_adjv1.TIF", 'label': " 5 - 10 ans" },
    { 'name': "SEN14_A1015_adjv1.TIF", 'label': "10 - 15 ans" },
    { 'name': "SEN14_A1520_adjv1.TIF", 'label': "15 - 20 ans" },
    { 'name': "SEN14_A2025_adjv1.TIF", 'label': "20 - 25 ans" },
    { 'name': "SEN14_A2530_adjv1.TIF", 'label': "25 - 30 ans" },
    { 'name': "SEN14_A3035_adjv1.TIF", 'label': "30 - 35 ans" },
    { 'name': "SEN14_A3540_adjv1.TIF", 'label': "35 - 40 ans" },
    { 'name': "SEN14_A4045_adjv1.TIF", 'label': "40 - 45 ans" },
    { 'name': "SEN14_A4550_adjv1.TIF", 'label': "45 - 50 ans" },
    { 'name': "SEN14_A5055_adjv1.TIF", 'label': "50 - 55 ans" },
    { 'name': "SEN14_A5560_adjv1.TIF", 'label': "55 - 60 ans" },
    { 'name': "SEN14_A6065_adjv1.TIF", 'label': "60 - 65 ans" },
    { 'name': "SEN14_A65PL_adjv1.TIF", 'label': "65 ans et plus" }
]


def count_all_populations(geometry, data_path, output = None):
    total_population = {}

    for file in sorted(data_files):
        if output:
            output.write("processing %s\n" % file['name'])
            output.flush()
        full_path = data_path + '/' + file['name']
        pop = count_population_file(full_path, geometry)
        if output:
            output.write("%s: %d\n" % (file['label'], pop))
            output.flush()
        total_population[file['label']] = str(pop)

    return total_population


if __name__ == "__main__":
    geoms = ''.join(sys.stdin.readlines())
    print(count_all_populations(json.loads(geoms), 'data'))
