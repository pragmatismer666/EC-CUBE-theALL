$(function(){
    //スマホメニュー
    $('header .hum').on('click', function(){
        if($(this).hasClass('open')){
            $(this).removeClass('open');
        } else {
            $(this).addClass('open');
        }
        $('.sp-navigation, .sp-navigation__overlay').addClass('active');
    })
    $(document).on('click', 'header .hum.open, .sp-navigation__overlay' ,function(){
        $('.sp-navigation, .sp-navigation__overlay').removeClass('active');
        $('header .hum').addClass('open');
    })


    $('.main-nav__top a, .cta_evwhr-btn__top, .sp_main-nav__top a, .smoothscroll').on('click', function(){
        var speed = 500;
        var href= $(this).attr("href");
        var target = $(href == "#" || href == "" ? 'html' : href);
        var position = target.offset().top - $('header').outerHeight();
        $("html, body").animate({scrollTop:position}, speed, "swing");
        return false;
    })

})


$(function(){
    var top

    var catNavSpacer = $('.shoplist-page__cont__category__spacer')
    var catNav = $('.shoplist-page__cont__category')
    var catNavHeight = catNav.outerHeight()

    var nameOrderSpacer = $('.shoplist-page__cont__nameorder__spacer')
    var nameOrder = $('.shoplist-page__cont__nameorder')
    var nameOrderHeight = nameOrder.outerHeight()


    settingCatNav(catNav)
    settingCatNav(nameOrder)

    if($('.shoplist-page').length){
        $(window).scroll(function(){
            top = $(this).scrollTop();

            if(top >= catNavSpacer.offset().top){
                catNav.addClass('fixed')
                catNavSpacer.css('height', catNavHeight + 'px')

                nameOrder.addClass('fixed')
                nameOrder.css('top', catNavHeight + 'px')
                nameOrderSpacer.css('height', nameOrderHeight + 'px')
            } else {
                catNav.removeClass('fixed')
                catNavSpacer.css('height', 'inherit')

                nameOrder.removeClass('fixed')
                nameOrderSpacer.css('height', 'inherit')
            }


            // nameOrder.find('a').removeClass('active')
            // $('#fashion .shoplist-page__cont__itemwrap__item').each(function(){
            //     var id = $(this).attr('id');
                
            //     if(top >= $(this).offset().top - catNavHeight - nameOrderHeight -1 && 
            //     top <= $(this).offset().top + $(this).outerHeight() - catNavHeight - nameOrderHeight){
            //         nameOrder.find('a[href="#'+ id + '"]').addClass('active');
            //     }
            // })


        })


        // $('.shoplist-page__cont__nameorder a, .shoplist-page__cont__category a').on('click', function(event) {
        //     if (this.hash !== "") {
        //       event.preventDefault();
        //       var hash = this.hash;
        //       $('html, body').animate({
        //         scrollTop: $(hash).offset().top - catNavHeight - nameOrderHeight
        //       }, 500);
        //     }
        // });


    }
    

    function settingCatNav(nav){
        nav.css('width',nav.outerWidth() + 'px')
    }
})