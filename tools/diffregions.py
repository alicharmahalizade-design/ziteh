"""Print horizontal bands of a pixelmatch diff image that contain differences."""
import sys
from PIL import Image
im = Image.open(sys.argv[1]).convert('RGB')
W, H = im.size
px = im.load()
rows = []
for y in range(H):
    c = 0
    for x in range(0, W, 2):
        r, g, b = px[x, y]
        if r > 200 and g < 80 and b < 80:
            c += 1
    rows.append(c)
bands, start = [], None
for y, c in enumerate(rows + [0]):
    if c and start is None:
        start = y
    elif not c and start is not None:
        bands.append((start, y, max(rows[start:y])))
        start = None
merged = []
for b in bands:
    if merged and b[0] - merged[-1][1] < 12:
        merged[-1] = (merged[-1][0], b[1], max(merged[-1][2], b[2]))
    else:
        merged.append(b)
for b in merged:
    print('y %5d-%5d  (h %4d)  max red px/row %d' % b[0:1] + (b[1], b[1] - b[0], b[2]) if False else 'y %5d-%5d  h %4d  maxrow %d' % (b[0], b[1], b[1] - b[0], b[2]))
