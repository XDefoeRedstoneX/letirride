@php
    // ────────────────────────────────────────────────────────────────
    //  Firewatch-style layered parallax city background (light mode).
    //  Four aligned full-frame landscape layers — sky → far skyline → mid
    //  buildings → ground street scene — each scrolls up at its own speed so
    //  the scene "descends" as you scroll, then the whole thing fades into the
    //  looping dark-concrete brick wall behind it.
    //  (far/mid/ground had their black backgrounds keyed to transparent so the
    //  sky shows through; the sky is the opaque backmost layer.)
    // ────────────────────────────────────────────────────────────────
    $bg = fn ($f) => asset('build/background/'.$f);
    $config = [
        // Back → front. `ratio` = how fast the layer slides up on scroll.
        'layers' => [
            ['img' => $bg('sky.jpg'),    'ratio' => 0.06],
            ['img' => $bg('far.png'),    'ratio' => 0.20],
            ['img' => $bg('mid.png'),    'ratio' => 0.38],
            ['img' => $bg('ground.png'), 'ratio' => 0.62],
        ],
        'brick' => $bg('bricklandwadhacjksda.png'),
    ];
@endphp

<div
    x-data="cityBg(@js($config))"
    class="fixed inset-0 overflow-hidden pointer-events-none -z-10"
    aria-hidden="true"
>
    {{-- Concrete wall behind everything; revealed as the city fades on scroll --}}
    <div class="absolute inset-0" :style="brickStyle()"></div>

    {{-- Parallax layers (sky is opaque and hides the concrete until you scroll) --}}
    <template x-for="(l, i) in layers" :key="i">
        <div class="absolute inset-0" :style="layerStyle(l)"></div>
    </template>
</div>

<script>
    function cityBg(config) {
        return {
            layers: config.layers,
            brick: config.brick,
            scrollY: 0,
            vh: window.innerHeight,
            fade: 1,
            _raf: null,

            init() {
                const onScroll = () => {
                    if (this._raf) return;
                    this._raf = requestAnimationFrame(() => {
                        this.scrollY = window.scrollY;
                        // Fade the whole scene out over ~1 screen → concrete takes over.
                        this.fade = Math.max(0, Math.min(1, 1 - this.scrollY / (this.vh * 0.9)));
                        this._raf = null;
                    });
                };
                window.addEventListener('scroll', onScroll, { passive: true });
                window.addEventListener('resize', () => { this.vh = window.innerHeight; }, { passive: true });
            },

            layerStyle(l) {
                const ty = -(this.scrollY * l.ratio);
                return {
                    backgroundImage: `url('${l.img}')`,
                    backgroundRepeat: 'no-repeat',
                    backgroundSize: 'cover',
                    backgroundPosition: 'center bottom',
                    transform: `translate3d(0, ${ty}px, 0)`,
                    opacity: this.fade,
                    willChange: 'transform, opacity',
                };
            },

            brickStyle() {
                return {
                    backgroundImage: `url('${this.brick}')`,
                    backgroundRepeat: 'repeat',
                    backgroundSize: 'clamp(280px, 32vw, 480px) auto',
                    backgroundPosition: 'top center',
                };
            },
        };
    }
</script>
