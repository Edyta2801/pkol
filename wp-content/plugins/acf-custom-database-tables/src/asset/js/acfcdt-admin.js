(function ($, window, document, undefined) {


    $(function () {
        var $textarea = $('#acfcdt-diagnostic-data'),
            visibleModifier = 'acfcdt-diagnostic-data--visible',
            $visBtn = $('#acfcdt-toggle-diagnostics'),
            ignoreFocus = false,
            toggleDiagnosticData = function () {
                if ($textarea.hasClass(visibleModifier)) {
                    $textarea.removeClass(visibleModifier);
                    $visBtn.text('Show Diagnostic Data');
                } else {
                    $textarea.addClass(visibleModifier);
                    $visBtn.text('Hide Diagnostic Data');
                }
            };
        $visBtn.click(function (e) {
            e.preventDefault();
            toggleDiagnosticData();
        });
        var $a = $('#acfcdt-diagnostic-copy');
        if (!document.queryCommandSupported('copy')) {
            return $a.remove();
        }
        $a.on('click', function (e) {
            e.preventDefault();
            $textarea.get(0).select();
            try {
                var copy = document.execCommand('copy');
                if (!copy) return;
                $('#acfcdt-copy-success').slideDown(160);
            } catch (err) {
            }
        });
    });


    $(function () {
        $('.acfcdt-doc-block').each(function () {
            var $this = $(this);
            var $title = $this.find('.acfcdt-doc-block-title');
            var $content = $this.find('.acfcdt-doc-block-content');
            $content.slideUp(0);
            $this.addClass('acfcdt-doc-block--closed');
            $title.click(function () {
                $content.slideToggle(160);
                $this.toggleClass('acfcdt-doc-block--closed acfcdt-doc-block--open');
            });
        });
    });


})(jQuery, window, document);