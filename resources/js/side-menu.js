document.addEventListener('DOMContentLoaded', function() {
    const HAMBURGER_ICON = document.getElementById('hamburger_icon');
    const CLOSE_ICON = document.getElementById('close_icon');
    let side_menu = document.getElementsByClassName("side_menu_off")[0];
    let main = document.getElementsByTagName("body")[0];

    if (!HAMBURGER_ICON || !CLOSE_ICON || !side_menu) {
        console.error('Side menu elements not found');
        return;
    }

    //サイドメニュー表示
    HAMBURGER_ICON.addEventListener('click', function(e) {
        e.stopPropagation();

        HAMBURGER_ICON.classList.add("hidden")
        CLOSE_ICON.classList.remove("hidden")

        // サイドメニュー表示
        side_menu.classList.remove("side_menu_off")
        side_menu.classList.add("side_menu_show")

        // mainをスクロール不可に
        main.classList.add("scroll_none")
    })

    // メニューのいずれかもしくは✗クリック時に画面グレー&サイドメニュー非表示
    let side_li = document.getElementsByClassName("side_li");
    for(let i= 0; i<side_li.length;i++){
        side_li[i].addEventListener("click",function (e){

            side_menu.classList.remove("side_menu_show")
            side_menu.classList.add("side_menu_off")

            HAMBURGER_ICON.classList.remove("hidden")
            CLOSE_ICON.classList.add("hidden")

            main.classList.remove("scroll_none")
        })
    }

    // 閉じるボタンのクリックイベント
    CLOSE_ICON.addEventListener('click', function(e) {
        e.stopPropagation();

        side_menu.classList.remove("side_menu_show")
        side_menu.classList.add("side_menu_off")

        HAMBURGER_ICON.classList.remove("hidden")
        CLOSE_ICON.classList.add("hidden")

        main.classList.remove("scroll_none")
    });

    // サイドメニューの外をクリックしたらサイドメニュー閉じる
    document.addEventListener("click",function (e){
        if(!e.target.closest('.side_wrapper') && !e.target.closest('#hamburger_icon')) {

            side_menu.classList.remove("side_menu_show")
            side_menu.classList.add("side_menu_off")

            HAMBURGER_ICON.classList.remove("hidden")
            CLOSE_ICON.classList.add("hidden")

            main.classList.remove("scroll_none")
        }
    })
});
