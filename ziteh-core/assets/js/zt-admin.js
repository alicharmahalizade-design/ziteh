/* Ziteh Core — admin screen */
(function ($) {
  'use strict';

  function reindex($rep) {
    var base = $rep.attr('data-name');
    $rep.find('> [data-zt-rep-rows] > [data-zt-rep-row]').each(function (i) {
      $(this).find('[name]').each(function () {
        var n = this.getAttribute('name');
        if (n.indexOf(base + '[') !== 0) return;
        var rest = n.slice(base.length).replace(/^\[[^\]]*\]/, '[' + i + ']');
        this.setAttribute('name', base + rest);
      });
    });
  }

  function bindSortable($rep) {
    var $rows = $rep.find('> [data-zt-rep-rows]');
    if (!$rows.sortable) return;
    $rows.sortable({ handle: '.zt-rep__drag', axis: 'y', placeholder: 'zt-rep__placeholder', update: function () { reindex($rep); } });
  }

  $(function () {
    $('[data-zt-rep]').each(function () { bindSortable($(this)); });

    $(document).on('click', '[data-zt-rep-add]', function () {
      var $rep = $(this).closest('[data-zt-rep]');
      var tpl = $rep.find('> [data-zt-rep-tpl]').html().replace(/<\\\/script>/g, '</script>');
      var i = $rep.find('> [data-zt-rep-rows] > [data-zt-rep-row]').length;
      var $row = $(tpl.replace(/__i__/g, i));
      $rep.find('> [data-zt-rep-rows]').append($row);
      $row.addClass('is-open').find('input,select,textarea').first().trigger('focus');
    });

    $(document).on('click', '[data-zt-rep-toggle]', function () {
      $(this).closest('[data-zt-rep-row]').toggleClass('is-open');
    });

    $(document).on('click', '[data-zt-rep-del]', function () {
      if (!window.confirm('این ردیف حذف شود؟')) return;
      var $rep = $(this).closest('[data-zt-rep]');
      $(this).closest('[data-zt-rep-row]').remove();
      reindex($rep);
    });

    $(document).on('click', '[data-zt-rep-dup]', function () {
      var $row = $(this).closest('[data-zt-rep-row]');
      var $rep = $row.closest('[data-zt-rep]');
      var $clone = $row.clone();
      // keep select/textarea values (clone() does not copy them)
      $row.find('select,textarea').each(function (k) { $clone.find('select,textarea').eq(k).val($(this).val()); });
      $row.after($clone);
      reindex($rep);
    });

    // live row titles
    $(document).on('input change', '[data-zt-rep-row] input, [data-zt-rep-row] select', function () {
      var $row = $(this).closest('[data-zt-rep-row]');
      var $t = $row.find('> .zt-rep__head [data-zt-rep-title]');
      var key = $t.attr('data-key');
      if (!key) return;
      var n = this.getAttribute('name') || '';
      if (n.slice(-key.length - 2) === '[' + key + ']') $t.text(this.value || 'ردیف جدید');
    });

    // colours
    $(document).on('input', '[data-zt-color-pick]', function () {
      $(this).siblings('[data-zt-color-text]').val(this.value);
    });
    $(document).on('input', '[data-zt-color-text]', function () {
      if (/^#[0-9a-f]{6}$/i.test(this.value)) $(this).siblings('[data-zt-color-pick]').val(this.value);
    });
    $(document).on('click', '[data-zt-color-reset]', function () {
      var v = this.getAttribute('data-zt-color-reset');
      $(this).siblings('[data-zt-color-text]').val(v).trigger('input');
    });

    // icons
    $(document).on('change', '[data-zt-icon]', function () {
      $(this).closest('.zt-iconsel').find('[data-zt-icon-prev]').attr('class', 'zti zti-' + this.value);
    });

    // media
    $(document).on('click', '[data-zt-media]', function (e) {
      e.preventDefault();
      if (!window.wp || !wp.media) return;
      var $w = $(this).closest('.zt-media');
      var frame = wp.media({ title: 'انتخاب تصویر', library: { type: 'image' }, button: { text: 'استفاده از این تصویر' }, multiple: false });
      frame.on('select', function () {
        var a = frame.state().get('selection').first().toJSON();
        $w.find('[data-zt-media-input]').val(a.url);
        $w.find('.zt-media__prev').attr('src', a.url).prop('hidden', false);
      });
      frame.open();
    });
    $(document).on('click', '[data-zt-media-clear]', function () {
      var $w = $(this).closest('.zt-media');
      $w.find('[data-zt-media-input]').val('');
      $w.find('.zt-media__prev').prop('hidden', true);
    });

    // unsaved changes guard
    var dirty = false;
    $('.zt-admin__form').on('change input', function () { dirty = true; }).on('submit', function () { dirty = false; });
    $(window).on('beforeunload', function () { if (dirty) return 'تغییرات ذخیره نشده‌اند.'; });
  });
})(jQuery);
