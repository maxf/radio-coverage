import rasterio
from rasterio.tools.mask import mask
import json
import sys
import numpy as np

geoms = json.load(sys.stdin)


def count_population(src, geoms):
    out_image, out_transform = mask(src, geoms['geometries'], crop=True)

    total = 0
    z = out_image[0]
    total = 0.0
    for band in z:
        for pixel in band:
            if pixel > 0:
                total += pixel
    return total


def count_population_file(filename, geoms):
    with rasterio.open(filename) as src:
        return count_population(src, geoms)

data = [
"SEN14_A0005_adjv1.TIF", "SEN14_A2530_adjv1.TIF", "SEN14_A5055_adjv1.TIF",
"SEN14_A0510_adjv1.TIF", "SEN14_A3035_adjv1.TIF", "SEN14_A5560_adjv1.TIF",
"SEN14_A1015_adjv1.TIF", "SEN14_A3540_adjv1.TIF", "SEN14_A6065_adjv1.TIF",
"SEN14_A1520_adjv1.TIF", "SEN14_A4045_adjv1.TIF", "SEN14_A65PL_adjv1.TIF",
"SEN14_A2025_adjv1.TIF", "SEN14_A4550_adjv1.TIF"
]

# load the raster, mask it by the polygon and crop it
#with rasterio.open("data/SEN14_A2025_adjv1.TIF") as src:

for filename in sorted(data):
    pop = count_population_file('data/'+filename, geoms)
    print(filename + ": " + str(int(pop)))

#out_meta = src.meta.copy()

# save the resulting raster
# out_meta.update({"driver": "GTiff",
#     "height": out_image.shape[1],
#     "width": out_image.shape[2],
# "transform": out_transform})

#with rasterio.open("masked.tif", "w", **out_meta) as dest:
#    dest.write(out_image)

# process the raster




#print out_meta
