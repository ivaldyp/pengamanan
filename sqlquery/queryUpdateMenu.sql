  select *
  from sec_menu

  
  update sec_menu
  set iconnew = 'fa-download'
  where ids = 32

  update sec_menu
  set iconnew = 'fa-building'
  where ids = 38

  update sec_menu
  set iconnew = 'fa-cogs'
  where ids = 18

  update sec_menu
  set iconnew = 'fa-home'
  where ids = 1

  update sec_menu
  set iconnew = 'fa-users'
  where ids = 2

  update sec_menu
  set iconnew = 'fa-wrench'
  where ids = 3

  update sec_menu
  set iconnew = 'fa-bar-chart-o'
  where ids = 42

  update sec_menu
  set urlnew = '/bpadwebs/home'
  where ids = 1

  update sec_menu
  set urlnew = '/bpadwebs/security/group user'
  where ids = 4

  update sec_menu
  set urlnew = '/bpadwebs/security/tambah user'
  where ids = 5

  update sec_menu
  set urlnew = '/bpadwebs/security/manage user'
  where ids = 6

  update sec_menu
  set urlnew = '/bpadwebs/cms/menu'
  where ids = 25

  update sec_menu
  set urlnew = '/bpadwebs/cms/kategori'
  where ids = 28

  update sec_menu
  set urlnew = '/bpadwebs/cms/subkategori'
  where ids = 29

  update sec_menu
  set urlnew = '/bpadwebs/cms/content'
  where ids = 31


  ----------------------------------------
  update sec_menu
  set urlnew = '/bpadwebs/kepegawaian/struktur'
  where ids = 17
