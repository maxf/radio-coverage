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
"SEN14_A0005_adjv1.TIF", "SEN14_A2530_adjv1.TIF", "SEN14_A5055_adjv1.TIF",
"SEN14_A0510_adjv1.TIF", "SEN14_A3035_adjv1.TIF", "SEN14_A5560_adjv1.TIF",
"SEN14_A1015_adjv1.TIF", "SEN14_A3540_adjv1.TIF", "SEN14_A6065_adjv1.TIF",
"SEN14_A1520_adjv1.TIF", "SEN14_A4045_adjv1.TIF", "SEN14_A65PL_adjv1.TIF",
"SEN14_A2025_adjv1.TIF", "SEN14_A4550_adjv1.TIF"
]


def count_all_populations(geoms,data_path):
    geometry = json.loads(geoms)
    total_population = ""

    for filename in sorted(data_files):
        full_path = data_path + '/' + filename
        pop = count_population_file(full_path, geometry)
        total_population += full_path + ': ' + str(pop) + '\n'

    return total_population


if __name__ == "__main__":
    geoms = json.load(sys.stdin)
    print(count_all_populations(geoms, 'data'))
