{{--
  Signature capture surface. Uses pointer events, so it works identically
  with a USB signature pad / stylus, a touchscreen, or a mouse — no vendor
  SDK required. Captures the raw stroke path (points + timestamps, plus
  pressure where the device reports it) alongside the flat PNG, because the
  path is much stronger evidence than the image alone.
--}}
<div class="sigpad" data-sigpad @if($required ?? false) data-sigpad-required @endif>
    <label>{{ $label ?? 'Signature' }}</label>
    <canvas data-sigpad-canvas class="sigpad-canvas"></canvas>
    <div class="row" style="margin-top:.5rem;">
        <span class="muted" data-sigpad-hint>Sign inside the box — pad, stylus, finger or mouse.</span>
        <button type="button" class="btn plain small row-end" data-sigpad-clear>Clear</button>
    </div>
    <input type="hidden" name="{{ $imageField ?? 'signature_capture' }}" data-sigpad-image>
    <input type="hidden" name="{{ $strokeField ?? 'signature_strokes' }}" data-sigpad-strokes>
</div>

@once
@push('scripts')
<script>
(function () {
  document.querySelectorAll('[data-sigpad]').forEach(function (root) {
    var canvas = root.querySelector('[data-sigpad-canvas]'),
        imageField = root.querySelector('[data-sigpad-image]'),
        strokeField = root.querySelector('[data-sigpad-strokes]'),
        clearBtn = root.querySelector('[data-sigpad-clear]'),
        hint = root.querySelector('[data-sigpad-hint]'),
        ctx, strokes = [], current = null, drawing = false, dirty = false, t0 = Date.now();

    function size() {
      // Back the canvas at device resolution so the captured signature is
      // crisp when it lands on the PDF, not a blurry upscale.
      var ratio = window.devicePixelRatio || 1,
          rect = canvas.getBoundingClientRect();
      canvas.width = Math.round(rect.width * ratio);
      canvas.height = Math.round(rect.height * ratio);
      ctx = canvas.getContext('2d');
      ctx.scale(ratio, ratio);
      ctx.lineWidth = 2.2; ctx.lineCap = 'round'; ctx.lineJoin = 'round'; ctx.strokeStyle = '#141924';
      redraw(rect.width, rect.height);
    }

    function redraw() {
      if (!ctx) return;
      strokes.forEach(function (s) {
        ctx.beginPath();
        s.points.forEach(function (p, i) { i ? ctx.lineTo(p.x, p.y) : ctx.moveTo(p.x, p.y); });
        ctx.stroke();
      });
    }

    function pos(e) {
      var r = canvas.getBoundingClientRect();
      return { x: e.clientX - r.left, y: e.clientY - r.top };
    }

    canvas.addEventListener('pointerdown', function (e) {
      e.preventDefault(); canvas.setPointerCapture(e.pointerId);
      drawing = true;
      var p = pos(e);
      current = { points: [{ x: p.x, y: p.y, t: Date.now() - t0, p: e.pressure || null }] };
      ctx.beginPath(); ctx.moveTo(p.x, p.y);
    });

    canvas.addEventListener('pointermove', function (e) {
      if (!drawing) return;
      e.preventDefault();
      var p = pos(e);
      current.points.push({ x: p.x, y: p.y, t: Date.now() - t0, p: e.pressure || null });
      ctx.lineTo(p.x, p.y); ctx.stroke();
      dirty = true;
    });

    function end(e) {
      if (!drawing) return;
      drawing = false;
      if (current && current.points.length) strokes.push(current);
      current = null;
      commit();
    }
    canvas.addEventListener('pointerup', end);
    canvas.addEventListener('pointercancel', end);
    canvas.addEventListener('pointerleave', end);

    function commit() {
      if (!dirty) return;
      imageField.value = canvas.toDataURL('image/png');
      strokeField.value = JSON.stringify({ strokes: strokes, capturedAt: new Date().toISOString() });
      hint.textContent = 'Signature captured.';
    }

    clearBtn.addEventListener('click', function () {
      strokes = []; dirty = false;
      imageField.value = ''; strokeField.value = '';
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      hint.textContent = 'Sign inside the box using the pad, stylus, finger or mouse.';
    });

    // Block submission of an empty pad rather than silently recording a
    // document as signed with no signature on it.
    var form = root.closest('form');
    if (form && root.hasAttribute('data-sigpad-required')) {
      form.addEventListener('submit', function (e) {
        if (!imageField.value) {
          e.preventDefault();
          hint.textContent = 'Please sign in the box before recording acceptance.';
          hint.style.color = '#a12622';
          canvas.scrollIntoView({ block: 'center', behavior: 'smooth' });
        }
      });
    }

    size();
    window.addEventListener('resize', function () { size(); });
  });
})();
</script>
@endpush
@endonce
