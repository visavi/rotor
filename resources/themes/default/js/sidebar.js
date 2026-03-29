$(document).ready(function () {
    var treeviewMenu = $('.app-menu');

    // Toggle Sidebar
    $('[data-bs-toggle="sidebar"]').click(function () {
        $('.app').toggleClass('sidenav-toggled');

        return false;
    });

    // Activate sidebar treeview toggle
    $('[data-bs-toggle="treeview"]').click(function () {
        if (!$(this).parent().hasClass('is-expanded')) {
            treeviewMenu.find('[data-bs-toggle="treeview"]').parent().removeClass('is-expanded');
        }
        $(this).parent().toggleClass('is-expanded');

        return false;
    });
});
