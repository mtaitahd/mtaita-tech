            </div>
        </div>

        <footer class="sticky-footer bg-transparent mt-auto">
            <div class="container my-auto">
                <div class="copyright text-center my-auto">
                    <span class="text-white-50 small">Copyright &copy; <?= date('Y') ?> — Mtaita Tech</span>
                </div>
            </div>
        </footer>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
<script src="assets/js/admin.js"></script>
<script>
// Live notification polling — checks for new unread messages every 15s
$(function () {
    function refreshUnread() {
        $.ajax({
            url: 'ajax_unread.php',
            dataType: 'json',
            success: function (data) {
                var count = parseInt(data.count) || 0;
                var bell = $('.notif-bell');
                var sidebar = $('.nav-item a[href="contacts"]').parent();
                var badgeText = count > 99 ? '99+' : count;

                // Update bell badge
                bell.find('.badge-counter').text(badgeText);
                bell.toggleClass('has-unread', count > 0);

                // Update sidebar badge
                sidebar.find('.badge-counter').remove();
                if (count > 0) {
                    sidebar.find('a').append('<span class="badge badge-danger badge-counter">' + badgeText + '</span>');
                }
                sidebar.toggleClass('msg-unread', count > 0);

                // Update alerts dropdown
                $('.dropdown-item[href="contacts"] .font-weight-bold').text(count + ' unread message(s)');
            }
        });
    }

    if ($('.notif-bell').length) {
        setInterval(refreshUnread, 15000);
    }
});
</script>
</body>
</html>
