@raster(
    width: fn () => 200,
    preview: true,
)
<script id="state" type="application/json">{!! json_encode(['width' => $raster->width()]) !!}</script>
