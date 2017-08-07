            </div>
        </div>
    </div>

    <!--footer starts here-->
    <div id="footer">

        <a href="/"><?= Setting::get('copy') ?></a><br />
        <?php
        show_online();
        show_counter();
        perfomance();
        ?>
    </div>
</div>
</body>
</html>

