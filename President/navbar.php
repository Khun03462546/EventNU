<!-- filepath: /path/to/navbar.php -->
<div class="nav-right">
                <button class="user-button" id="userButton">
                    <img src="../assets/imgs/jusmine.png" alt="Profile" class="profile-img">
                    <span><?php echo htmlspecialchars($firstname); ?></span>
                </button>

                <div class="user-menu" id="userMenu">
                    <div class="d-flex align-items-center px-3 py-2">
                        <img src="../assets/imgs/jusmine.png" alt="Profile" class="profile-img">
                        <div>
                            <p class="m-0"><?php echo htmlspecialchars($firstname); ?><br>
                                <small><?php echo htmlspecialchars($email); ?></small></p>
                        </div>
                    </div>
                    <hr>
                    <a href="eventAllpre.php">อีเว้นท์ทั้งหมด</a>
                    <a href="checkBack.php">คำสั่งซื้อทั้งหมด</a>
                    <a href="followSum.php">ติดตามอีเว้นท์</a>
                    <a href="setting.php">ข้อมูลส่วนตัว</a>
                    <hr>
                    <a href="../logout.php" class="text-danger">ออกจากระบบ</a>
                </div>
            </div>
        </div>
    </nav>