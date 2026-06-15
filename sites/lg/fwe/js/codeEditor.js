
// ---------- Настройки редактора ----------
function resizeEditors() {
    const ehh = $('#editor_header').outerHeight();
    const ehc = $('#content-panel').outerHeight();
    $('.fwe_data').each(function() {
        $(this).height(ehc - ehh - 36);
    });
    Object.values(TabsManager.tabsarray).forEach(function(tab) {
        if (tab.editor && typeof tab.editor.resize === "function") tab.editor.resize();
    });
}
$('#fwe_eopt__wrap').on('change', function() {
    const wrap = $(this).is(':checked');
    Object.values(TabsManager.tabsarray).forEach(tab => {
        if (tab.editor) tab.editor.getSession().setUseWrapMode(wrap);
    });
});
$('#fwe_eopt__fontsize').on('input', function() {
    const size = $(this).val();
    Object.values(TabsManager.tabsarray).forEach(tab => {
        if (tab.editor) tab.editor.setFontSize(size + "px");
    });
});
$('#fwe_eopt__theme').on('change', function() {
    const theme = $(this).val() || 'monokai';
    Object.values(TabsManager.tabsarray).forEach(tab => {
        if (tab.editor) tab.editor.setTheme("ace/theme/" + theme);
    });
});
