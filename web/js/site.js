$(document).ready(() => {
    $('.copy-to-clipboard-by-click').click((e) => {
        const $temp = $("<input>");
        $("body").append($temp);
        $temp.val($($(e.target).data('target')).html()).select();
        document.execCommand("copy");
        $temp.remove();
        $(e.target).text('[ Link copied! ]');
        setTimeout(() => {
            $(e.target).text('copy following again');
        }, 5000);
    });
});
