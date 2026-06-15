<header class="header-lk">
    <div class="container">
        <div class="header-lk-main">
            <div id="btn-lk" class="menu-lk">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <div class="header-account">
                <a href="#" class="header-account__profile">{{ Auth::user()->login ?? 'Guest' }}</a>
                <a href="{{ route('auth.logout') }}" class="header-account__logout"></a><br/>
            </div>
        </div>
    </div>
</header>
<style>
/* Additional styles for header-lk */
.header-account {
    text-align: right;
}
.header-account__site-link {
    display: inline-block;
    margin-top: 5px;
    font-size: 12px;
    color: #00CDAD;
    text-decoration: none;
    transition: color 0.3s ease;
}

.header-account__site-link:hover {
    color: #00B89A;
    text-decoration: underline;
}
</style>
