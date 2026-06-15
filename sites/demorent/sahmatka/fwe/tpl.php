<html>
 
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <script src="https://code.jquery.com/jquery-3.6.3.js"></script>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js"></script>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.9.6/ace.js" integrity="sha512-czfWedq9cnMibaqVP2Sw5Aw1PTTabHxMuTOkYkL15cbCYiatPIbxdV0zwhfBZKNODg0zFqmbz8f7rKmd6tfR/Q==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <link href="https://cdn.jsdelivr.net/npm/ace-builds@1.15.1/css/ace.min.css" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.9.6/ext-modelist.min.js"></script>
  <!-- Определение типа подсветки -->

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.2.3/js/bootstrap.min.js"></script>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.14/themes/default-dark/style.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/themes/default/style.min.css" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.14/jstree.min.js"></script>

  <script src="https://code.jquery.com/ui/1.13.1/jquery-ui.min.js" integrity="sha256-eTyxS0rkjpLEo16uXTS0uVCS4815lc40K2iVpWDvdSY=" crossorigin="anonymous"></script>

 
 
  <!-- Шрифт иконок -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/MaterialDesign-Webfont/7.1.96/css/materialdesignicons.min.css"   />
<style>
/*
https://pictogrammers.com/library/mdi/icon/delete-circle-outline/
*/
.mdi.fs21{font-size:21px;}
.mdi.red{color:red;}
 
 
    
		
    body {
      height: 100vh;
      background-color: #333;
      margin: 0;
    }
    
    .header,
    .foother {
      color: #FFF;
      background: #444;
    }
    
    .tree_dir {
      font-weight: bold;
    }
    
    .nav {
      max-height: 100%;
      min-width: 250px;
      overflow-y: scroll;
    }
    
    .treeview ul {
      background-color: initial;
      margin-top: 4px;
    }
    
    .treeview li {}
    
    .tree_filea {
      color: #FFF;
      font-family: arial;
      font-weight: 100;
      font-size: 12px;
      text-decoration: none;
      display: inline;
      margin-top: 0;
    }
    
    .tree_file_s {
      border: solid 1px #FFF;
    }
    
    .tree_dira {
      color: #EEE;
      font-family: arial;
      font-weight: bold;
      font-size: 12px;
      text-decoration: none;
      display: inline;
      margin-top: 0;
    }
    
    #loaderwrapper {
      width: 44px;
      height: 32px;
    }
    
    #loader {
      display: none;
    }
    
    #editorstatus {
      width: 100%;
      height: 490px;
      overflow-y: scroll;
      background-color: #000;
      color: #CF5EF6;
      font-family: arial;
      font-size: 11px;
      line-height: 20px;
      padding-left: 10px;
    }
    
    form {
      margin: 0;
    }
    
    #f {
      width: 100%;
      background: #000;
      color: #fff;
    }
    
    .fwe_tabhead_link {
      position: relative;
      white-space: nowrap;
      display: inline-block;
      display: inline-block;
      background: #444;
      padding: 5px;
      padding-right: 15px;
      border: solid 1px #444;
      margin: 1px;
      font-family: arial;
      font-size: 12px;
      cursor: pointer;
      color: #75715e;
    }
    
    .fwe_tabhead_link_active {
      color: #FFF;
    }
    
    .fwe_tabhead_link_orig {
      color: #FFF;
    }
    /* не измененнок содержимое*/
    
    .fwe_tabhead_link_notsave {
      color: red;
    }
    /* не сохраненное измененное */
    
    .tabclose {
      color: red;
      font-family: red;
      cursor: pointer;
      display: block;
      position: absolute;
      right: 0;
      top: 0;
      font-family: arial;
      padding: 2px;
      border: solid 1px #444;
      border-radius: 25px;
    }
    
    .tabclose:hover {
      border: solid 1px #555;
      border-radius: 25px;
      background: #555;
    }
    
    #tabs_headers {
      background-color: #333;
    }
    
    .fwe_tabbody {
      display: inline-block;
      display: none;
      min-height: auto;
    }
    
    .fwe_data {
      width: 100%;
      display: block;
      min-height: 200px;
    }
    /* textarea */
    
    .topbotton {
      border: solid 1px;
      #fff;
      color: #fff;
      text-decoration: none;
      display: inline-block;
      padding: 2px;
      font-size: 10px;
      min-width: 50px;
      line-height: 23px;
      height: 30px;
      min-width: 30px;
      text-align: center;
    }
    
    .opened_file {
      border: solid 1px #EEE;
    }
    
    .opened_file_unsave {
      border: solid 1px red;
    }
    
    .finput,
    fwe_thisfile {
      width: 100%;
      background-color: #000;
      color: #999;
      font-size: 10px;
      border: none;
    }
    /*Размер шрифта редактора*/
    
    .CodeMirror * {
      font-size: 12px;
      font-family: "Courier New", Courier, "Lucida Sans Typewriter", "Lucida Typewriter", monospace;
      line-height: 1.3em;
    }
    
    .sp_nav,
    .spp_nav,
    #side-checkbox1,
    #side-checkbox2,
    #side-checkbox3 {
      display: none;
    }
    
    .side-panel,
    .content-panel {
      height: 100vh;
      overflow-y: scroll;
      scrollbar-arrow-color: #fff;
      /* Цвет стрелок */
      scrollbar-base-color: #ffa12d;
      /* Цвет полосы прокрутки */
      scrollbar-shadow-color: #fed189;
      /* Цвет тени */
      scrollbar-highlight-color: #fff;
      /* Цвет светлых участков тени */
      padding: 0;
      ::-webkit-scrollbar {
        /* 1 */
      }
      ::-webkit-scrollbar-button {
        /* 2 */
      }
      ::-webkit-scrollbar-track {
        /* 3 */
      }
      ::-webkit-scrollbar-track-piece {
        /* 4 */
      }
      ::-webkit-scrollbar-thumb {
        /* 5 */
      }
      ::-webkit-scrollbar-corner {
        /* 6 */
      }
      ::-webkit-resizer {
        /* 7 */
      }
    }
    
    .spanel_heder {
      position: sticky;
      top: 0;
      background-color: #444;
      border-bottom: 1px solid #222;
      z-index: 10;
      opacity: 0.95;
      width: 100%;
      padding: 5px;
    }
    
    .spanel_content {
      padding: 5px;
    }
    
    @media (max-width: 570px) {
      .content-panel {
        width: 100%;
      }
      /* Оформление боковой панели */
      .side-panel,
      .side-panel2,
      .side-panel3 {
        position: fixed;
        transition: all 0.5s;
        z-index: 1001;
        background: #333;
        box-shadow: 5px 0 5px rgba(0, 0, 0, 0.4);
        color: #FFF;
        padding: 0;
      }
      /* Левая панель */
      .side-panel1 {
        top: 0;
        left: -100vw;
        width: 100vw;
        height: 100vh;
        padding-left: 37px;
      }
      /* Правая панель */
      .side-panel2 {
        top: 0;
        right: -100vw;
        width: 100vw;
        height: 100vh;
        padding-right: 37px;
        padding-left: 5px;
        padding-top: 10px;
      }
      .side-title {
        font-size: 20px;
        padding-bottom: 10px;
        margin-bottom: 20px;
        border-bottom: 2px solid #BFE2FF;
      }
      #side-checkbox1:checked + .side-panel1 {left: 0;}
	  #side-checkbox1:checked + .side-panel1 .spp_nav {position:fixed;}
	   
      #side-checkbox2:checked + .side-panel2 {right: 0;}
	  
      #side-checkbox3:checked + .side-panel3 {bottom: 0;}
	  #side-checkbox1:checked + .side-panel3 .spp_nav {position:fixed;}
	  
      /* Оформление кнопки на панеле */
      .spp_nav {
        height: 100vh;
        display: block;
        position: absolute;
        z-index: 1;
        cursor: pointer;
        background-color: #000;
        opacity: 0.5;
        transition: all 180ms ease-in-out;
        width: 30px;
      }
      .spp_nav span {
        transform: rotate(45deg);
        color: #FFF;
        font-size: 30px;
        position: absolute;
        left: 7px;
        top: 25vh;
      }
      .spp_nav label:hover {
        transform: rotate(45deg) scale(1.1);
        color: #FFF;
      }
      .sp_nav {
        display: block;
        font-weight: bold;
        padding-left: 0;
        padding-right: 0;
      }
      .sp_nav label {
        width: 100%;
        text-align: center;
      }
      .spp_nav1 {
        top: 0;
        left: 0;
      }
      .spp_nav2 {
        top: 0;
        right: 0;
      }
      /* Внешние кнопки открытия */
      .sp_nav1 {
        border-top-right-radius: 15px;
        border-bottom-right-radius: 15px;
        position: fixed;
        top: 25vh;
        opacity: 0.5;
        z-index: 999;
        left: 0px;
        display: block;
        background-color: #000;
        width: 30px;
        height: 50px;
        line-height: 50px;
        color: #FFF;
      }
      .sp_nav2 {
        border-top-left-radius: 15px;
        border-bottom-left-radius: 15px;
        position: fixed;
        top: 25vh;
        opacity: 0.5;
        z-index: 999;
        right: 0px;
        display: block;
        background-color: #000;
        width: 30px;
        height: 50px;
        line-height: 50px;
        color: #FFF;
      }
      .sp_nav3 {
        position: fixed;
        button: 0;
        opacity: 0.5;
        z-index: 999;
        left: 0px;
        display: block;
        background-color: #000;
        width: 30px;
        height: 50px;
        line-height: 50px;
        color: #FFF;
      }
    }
    
    #filesajaxtree .folder {
      background: url('file_sprite.png') right bottom no-repeat;
    }
    
    #filesajaxtree .file {
      background: url('file_sprite.png') 0 0 no-repeat;
    }
    
    #filesajaxtree .file-pdf {
      background-position: -32px 0
    }
    
    #filesajaxtree .file-as {
      background-position: -36px 0
    }
    
    #filesajaxtree .file-c {
      background-position: -72px -0px
    }
    
    #filesajaxtree .file-iso {
      background-position: -108px -0px
    }
    
    #filesajaxtree .file-htm,
    #filesajaxtree .file-html,
    #filesajaxtree .file-xml,
    #filesajaxtree .file-xsl {
      background-position: -126px -0px
    }
    
    #filesajaxtree .file-cf {
      background-position: -162px -0px
    }
    
    #filesajaxtree .file-cpp {
      background-position: -216px -0px
    }
    
    #filesajaxtree .file-cs {
      background-position: -236px -0px
    }
    
    #filesajaxtree .file-sql {
      background-position: -272px -0px
    }
    
    #filesajaxtree .file-xls,
    #filesajaxtree .file-xlsx {
      background-position: -362px -0px
    }
    
    #filesajaxtree .file-h {
      background-position: -488px -0px
    }
    
    #filesajaxtree .file-crt,
    #filesajaxtree .file-pem,
    #filesajaxtree .file-cer {
      background-position: -452px -18px
    }
    
    #filesajaxtree .file-php {
      background-position: -108px -18px
    }
    
    #filesajaxtree .file-jpg,
    #filesajaxtree .file-jpeg,
    #filesajaxtree .file-png,
    #filesajaxtree .file-gif,
    #filesajaxtree .file-bmp {
      background-position: -126px -18px
    }
    
    #filesajaxtree .file-ppt,
    #filesajaxtree .file-pptx {
      background-position: -144px -18px
    }
    
    #filesajaxtree .file-rb {
      background-position: -180px -18px
    }
    
    #filesajaxtree .file-text,
    #filesajaxtree .file-txt,
    #filesajaxtree .file-md,
    #filesajaxtree .file-log,
    #filesajaxtree .file-htaccess {
      background-position: -254px -18px
    }
    
    #filesajaxtree .file-doc,
    #filesajaxtree .file-docx {
      background-position: -362px -18px
    }
    
    #filesajaxtree .file-zip,
    #filesajaxtree .file-gz,
    #filesajaxtree .file-tar,
    #filesajaxtree .file-rar {
      background-position: -416px -18px
    }
    
    #filesajaxtree .file-js {
      background-position: -434px -18px
    }
    
    #filesajaxtree .file-css {
      background-position: -144px -0px
    }
    
    #filesajaxtree .file-fla {
      background-position: -398px -0px
    }
    
    .jstree-default-dark .jstree-hovered {
      color: #EEE;
      font-size: 12px;
      background: #333;
    }
    
    .jstree-default-dark .jstree-clicked {
      background: #333;
      border-radius: 1px;
      box-shadow: inset 0 0 1px #999;
    }
    
    .jstree-default-dark .jstree-icon:empty {
      width: 19px;
	  
	  height:20px;
      margin-top: 2px;
	  
    }
    
    .jstree-default-dark .jstree-anchor {
      color: #EEE;
      font-size: 12px;
/*     
	 line-height: 25px;
      height: 25px;
      margin: 7px;
*/  
  }
  </style>
</head>

<body>

  <section>

    <div class="container-fluid">

      <div class="row">

        <div class="sp_nav sp_nav1">
          <label for="side-checkbox1">F </label>
        </div>
        <input type="checkbox" id="side-checkbox1" />
        <div class="col-2 side-panel side-panel1">
          <label class="spp_nav spp_nav1" for="side-checkbox1"><span>+</span></label>
          <div class="spanel_heder">
            <?=$fw_tpl->attr('leftpanel');?>
          </div>
          <div class="spanel_content">
            <div id="filesajaxtree"></div>
          </div>
        </div>

        <div class="col-8 content-panel" id="content-panel">

          <div class="spanel_heder " id="editor_header">
            <div id="tabs_headers"></div>
            <table width="100%">
              <tr>
                <td style="width:50px;">
                  <div id="loaderwrapper"><img src="loader.gif" id="loader"></div>
                </td> 
                <td style="text-align:left;">
                  <a href="" class="topbotton ico" id="fwe_save"><span class="mdi mdi-content-save-all fs21"></span>  </a>

                  <a href="" class="topbotton ico" id="frw_unfo"><span class="mdi mdi-undo fs21"></span> </a>

                  <a href="" class="topbotton ico" id="frw_redo"><span class="mdi mdi-redo fs21"></span></a>
				  
				  <a href="" class="topbotton ico" id="frw_redo"><span class="mdi mdi-delete-outline fs21"></span></a>
				 <span class="mdi mdi-delete-restore"></span>
				 
<span class="mdi mdi-delta fs21"></span>
<span class="mdi mdi-history fs21"></span>
<span class="mdi mdi-download fs21"></span>
<span class="mdi mdi-upload fs21"></span>
<span class="mdi mdi-rename fs21"></span>
<span class="mdi mdi-folder-plus-outline fs21"></span>
<span class="mdi mdi-file-code-outline fs21"></span>
<span class="mdi mdi-file-document-outline fs21"></span>

                </td>
                <td style="text-align: right;">
                  <a href="" class="topbotton" id="fwe_closeall">Закрыть все</a>
                </td>
              </tr>
            </table>

          </div>
          <div class="spanel_content">

            <div id="tabs_body"></div>
          </div>

        </div>

        <div class="sp_nav sp_nav2">
          <label for="side-checkbox2">H</label>
        </div>
        <input type="checkbox" id="side-checkbox2" />
        <div class="col-2 side-panel side-panel2">
          <label class="spp_nav spp_nav2" for="side-checkbox2"><span>+</span></label>

          <div class="spanel_heder">123</div>
          <div class="spanel_content">
            <style>
              .accordion-item {
                border: 1px solid #ddd;
                margin: 5px;
                border: none;
              }
              
              .ui-accordion-header {
                padding: 5px;
                background: #444;
                color: #EEE;
                font-size: 12px;
                cursor: pointer;
                outline: none;
              }
              
              .ui-accordion-content {
                padding: 3px;
                background: #555;
                border: solid 1px #444;
              }
              
              .portlet-placeholder {}
            </style>

            <div class="fwe_eopt">

              <div class="accordion-root">
                <div class="accordion">
                  <div class="accordion-item">
                    <div class="accordion-header ico"><span class="mdi mdi-cog-outline fs21"></span>Настройки редактора</div>
                    <div>
                      <div class="fwe_eopt_opt">
                        <label for="fwe_eopt__wrap">Перенос по словам:
                          <input type="checkbox" id="fwe_eopt__wrap" value="1">
                        </label>
                      </div>
                      <div class="fwe_eopt_opt">Размер шрифта:
                        <input type="number" min="7" max="20" value="12">
                      </div>
                      <div class="fwe_eopt_opt">Тема:
                        <select id="fwe_eopt__theme">
                          <option value="">Темная</option>
                          <option value="">Светлая</option>
                        </select>
                      </div>

                    </div>
                  </div>
                </div>

              </div>

            </div>

            <div id="editorstatus"></div>
          </div>

        </div>

      </div>
    </div>
  </section>

  <script type="text/javascript">
    //https://ace.c9.io/api/interfaces/Ace.EditorOptions.html
    //https://ace.c9.io/#nav=howto
    //https://github.com/ajaxorg/ace/wiki/Configuring-Ace
    //https://code.tutsplus.com/tutorials/adding-a-custom-css-editor-to-your-theme-using-ace--wp-29451
    $(function() {
      $('.accordion').accordion({
        header: '> .accordion-item > .accordion-header',
        heightStyle: 'content',
        active: true,
        collapsible: true
      });

      function print_r(obj) {
        str = JSON.stringify(obj);
        //str = JSON.stringify(obj, null, 4); // (Optional) beautiful indented output.
        console.log(str); // Logs output to dev tools co
      }
      $('#filesajaxtree').jstree({
        'core': {
          'data': {
            "url": "index.php?act=jsoonajaxftree&ajax=1",
            'data': function(node) {
              return {
                'id': node.id
              };
            },
            "dataType": "json" // needed only if you do not supply JSON headers
          },
          "themes": {
            "name": "default-dark",
            "dots": true,
            "icons": true,
            'responsive': false,
            'variant': 'medium',
            'stripes': true
          },
        },
        'sort': function(a, b) {
          return this.get_type(a) === this.get_type(b) ? (this.get_text(a) > this.get_text(b) ? 1 : -1) : (this.get_type(a) >= this.get_type(b) ? 1 : -1);
        },
        'types': {
          'default': {
            'icon': 'folder'
          },
          'file': {
            'valid_children': [],
            'icon': 'file'
          }
        },
        'unique': {
          'duplicate': function(name, counter) {
            return name + ' ' + counter;
          }
        },
        'plugins': ['state', 'dnd', 'sort', 'types', 'unique','wholerow']
      });
      //$.jstree.defaults.core.multiple
      // ВЫДЕЛЕНИЕ ВЕТКИ ДЕРЕВА
      $('#filesajaxtree').on('select_node.jstree', function(e, data) {
        // print_r(data.node );
        ///alert(data.node.a_attr.dtype);
        if (data.node.a_attr.dtype == "file") // файл - ОТКРЫВАЕМ
        {
          var file = data.node.a_attr.dpath;
          var url = 'index.php?ajax=1&act=loadfile&f=' + file;
          var caption = data.node.text;
          var ti = fwe_addtab(file, caption); // Новая вкладка пробуем открыть и получаем ID
          if (ti) // Если удалось создать вкладку
          {
            loadfileeditor(url, file, 'fwe_bi_dataf_' + ti, ti); // Грузим редактор в ид 
          }
          return false;
        }
      });
      // пЕРЕХОД ПО ТРЕКУ ПОЛУЧАЕМ ТРЕК ИЗ ТЕКСТОВОГО ПОЛЯ И циклом проверяем открыт ли уровень если не отрыт открываем? лучше пока в jsoon на сервеной стороне?
      $('.spanel_heder').on("click", function() {
        //alert(1);
        //var instance = $('#filesajaxtree').jstree(true);
        //instance.deselect_all();
        //instance.select_node('sites/em/sahmatka/fwe/editor-main/assets/fonts/glyphicons-halflings-regular.eot');
      });
      $(function() {
        $('#tree')
          .jstree({
            'core': {
              'data': {
                'url': '?operation=get_node',
                'data': function(node) {
                  return {
                    'id': node.id
                  };
                }
              },
              'check_callback': function(o, n, p, i, m) {
                if (m && m.dnd && m.pos !== 'i') {
                  return false;
                }
                if (o === "move_node" || o === "copy_node") {
                  if (this.get_node(n).parent === this.get_node(p).id) {
                    return false;
                  }
                }
                return true;
              },
              'themes': {
                'responsive': false,
                'variant': 'small',
                'stripes': true
              }
            },
            'sort': function(a, b) {
              return this.get_type(a) === this.get_type(b) ? (this.get_text(a) > this.get_text(b) ? 1 : -1) : (this.get_type(a) >= this.get_type(b) ? 1 : -1);
            },
            'contextmenu': {
              'items': function(node) {
                var tmp = $.jstree.defaults.contextmenu.items();
                delete tmp.create.action;
                tmp.create.label = "New";
                tmp.create.submenu = {
                  "create_folder": {
                    "separator_after": true,
                    "label": "Folder",
                    "action": function(data) {
                      var inst = $.jstree.reference(data.reference),
                        obj = inst.get_node(data.reference);
                      inst.create_node(obj, {
                        type: "default"
                      }, "last", function(new_node) {
                        setTimeout(function() {
                          inst.edit(new_node);
                        }, 0);
                      });
                    }
                  },
                  "create_file": {
                    "label": "File",
                    "action": function(data) {
                      var inst = $.jstree.reference(data.reference),
                        obj = inst.get_node(data.reference);
                      inst.create_node(obj, {
                        type: "file"
                      }, "last", function(new_node) {
                        setTimeout(function() {
                          inst.edit(new_node);
                        }, 0);
                      });
                    }
                  }
                };
                if (this.get_type(node) === "file") {
                  delete tmp.create;
                }
                return tmp;
              }
            },
            'types': {
              'default': {
                'icon': 'folder'
              },
              'file': {
                'valid_children': [],
                'icon': 'file'
              }
            },
            'unique': {
              'duplicate': function(name, counter) {
                return name + ' ' + counter;
              }
            },
            'plugins': ['state', 'dnd', 'sort', 'types', 'contextmenu', 'unique']
          })
          .on('delete_node.jstree', function(e, data) {
            $.get('?operation=delete_node', {
                'id': data.node.id
              })
              .fail(function() {
                data.instance.refresh();
              });
          })
          .on('create_node.jstree', function(e, data) {
            $.get('?operation=create_node', {
                'type': data.node.type,
                'id': data.node.parent,
                'text': data.node.text
              })
              .done(function(d) {
                data.instance.set_id(data.node, d.id);
              })
              .fail(function() {
                data.instance.refresh();
              });
          })
          .on('rename_node.jstree', function(e, data) {
            $.get('?operation=rename_node', {
                'id': data.node.id,
                'text': data.text
              })
              .done(function(d) {
                data.instance.set_id(data.node, d.id);
              })
              .fail(function() {
                data.instance.refresh();
              });
          })
          .on('move_node.jstree', function(e, data) {
            $.get('?operation=move_node', {
                'id': data.node.id,
                'parent': data.parent
              })
              .done(function(d) {
                //data.instance.load_node(data.parent);
                data.instance.refresh();
              })
              .fail(function() {
                data.instance.refresh();
              });
          })
          .on('copy_node.jstree', function(e, data) {
            $.get('?operation=copy_node', {
                'id': data.original.id,
                'parent': data.parent
              })
              .done(function(d) {
                //data.instance.load_node(data.parent);
                data.instance.refresh();
              })
              .fail(function() {
                data.instance.refresh();
              });
          })
          .on('changed.jstree', function(e, data) {
            if (data && data.selected && data.selected.length) {
              $.get('?operation=get_content&id=' + data.selected.join(':'), function(d) {
                if (d && typeof d.type !== 'undefined') {
                  $('#data .content').hide();
                  switch (d.type) {
                    case 'text':
                    case 'txt':
                    case 'md':
                    case 'htaccess':
                    case 'log':
                    case 'sql':
                    case 'php':
                    case 'js':
                    case 'json':
                    case 'css':
                    case 'html':
                      $('#data .code').show();
                      $('#code').val(d.content);
                      break;
                    case 'png':
                    case 'jpg':
                    case 'jpeg':
                    case 'bmp':
                    case 'gif':
                      $('#data .image img').one('load', function() {
                        $(this).css({
                          'marginTop': '-' + $(this).height() / 2 + 'px',
                          'marginLeft': '-' + $(this).width() / 2 + 'px'
                        });
                      }).attr('src', d.content);
                      $('#data .image').show();
                      break;
                    default:
                      $('#data .default').html(d.content).show();
                      break;
                  }
                }
              });
            } else {
              $('#data .content').hide();
              $('#data .default').html('Select a file from the tree.').show();
            }
          });
      });
      // ПОДГОНЯЕМ РАЗМЕР РЕДАКТОРА		
      $(window).resize(function() {
        var ehh = $('#editor_header').outerHeight();
        var ehc = $('#content-panel').outerHeight();
        $('.fwe_data').css('height', ehc - ehh - 30 + 'px');
        //editor.resize()
      });
      /*
      Codemiror div переделать в textarea и заменить метод на generator 
		
      */
      function generate_editor2(obj_id) {
        var ehh = $('#editor_header').outerHeight();
        var ehc = $('#content-panel').outerHeight();
        $('.fwe_data').css('height', ehc - ehh - 30 + 'px');
        // https://ace.c9.io/#nav=howto
        var aceditor = ace.edit(obj_id); ///////////// КОСЯК ТУТ
        aceditor.session.setMode('ace/mode/php');
        aceditor.session.setTabSize(4);
        aceditor.session.setUseWrapMode(true); // Перенос по словам
        aceditor.setShowPrintMargin(false); // Полу печати выкл
        return aceditor;
      }

      function generate_editor(obj_id) {
        var widgets = []

        function createLineWidget(line, message) {
          var msg = document.createElement("div");
          var icon = msg.appendChild(document.createElement("span"));
          icon.innerHTML = "!!";
          icon.className = "lint-error-icon";
          msg.appendChild(document.createTextNode(message));
          msg.className = "lint-error";
          widgets.push(cmeditor.addLineWidget(line, msg, {
            coverGutter: false,
            noHScroll: true
          }));
        }
        // bind key "F9" as "submit & run code"
        CodeMirror.keyMap.LiveEditor = {
          'F9': function(cm) {
            submitCode();
          },
          fallthrough: 'pcDefault'
        };
        var cmeditor = CodeMirror.fromTextArea(document.getElementById(obj_id), {
          matchBrackets: true,
          autoCloseTags: true,
          /* Автозакрывание тегов*/
          lineNumbers: true,
          lineWrapping: false,
          /* Перенос строк */
          styleActiveLine: true,
          /* Addon */
          mode: 'php',
          linter: true,
          matchBrackets: true,
          /* сопоставление скобок */
          indentWithTabs: true,
          enterMode: 'keep',
          keyMap: 'LiveEditor',
          theme: 'monokai',
          tabMode: 'shift',
          gutters: ["CodeMirror-lint-markers", "CodeMirror-linenumbers"],
          onCursorActivity: function() {
            cmeditor.addLineClass(hlLine, null);
            hlLine = cmeditor.addLineClass(cmeditor.getCursor().line, "CodeMirror-activeline-background");
          }
        });
        cmeditor.focus();
        cmeditor.setCursor({
          line: 1
        });
        //mode: "application/x-httpd-php-open",
        var ehh = $('#editor_header').outerHeight();
        var ehc = $('#content-panel').outerHeight();
        cmeditor.setSize('100%', ehc - ehh - 30 + 'px'); // w,h
        function changemode(val = 'xxx.php') {
          var m, mode, spec;
          if (m = /.+\.([^.]+)$/.exec(val)) {
            var info = CodeMirror.findModeByExtension(m[1]);
            if (info) {
              mode = info.mode;
              spec = info.mime;
            }
          } else if (/\//.test(val)) {
            var info = CodeMirror.findModeByMIME(val);
            if (info) {
              mode = info.mode;
              spec = val;
            }
          } else {
            mode = spec = val;
          }
          if (mode) {
            alert(mode);
            //editor.setOption("mode", spec);
            // CodeMirror.autoLoadMode(editor, mode);
          }
        }
        //changemode() ;
        return cmeditor;
      }
      /* - ------------------------------ ВКЛАДКИ РЕДАКТОРЫ------------------------------------------ */
      //Запись лога
      function addlog(text, nowrite = false) {
        Data = new Date();
        Year = Data.getFullYear();
        Month = Data.getMonth();
        Day = Data.getDate();
        Hour = Data.getHours();
        Minutes = Data.getMinutes();
        Seconds = Data.getSeconds();
        $('#editorstatus').prepend(Hour + ':' + Minutes + ':' + Seconds + '&nbsp;&nbsp;  ' + text + ' <br/>');
      }
      class fwetab {
        //constructor() { alert(1); }
        method1() {}
      }
      var global_ti; // Текущая активная вкладка индекс!
      // Массив объектов вкладок
      var tabsarray = new Object();
      var url_tabsarray = new Object(); // [file] = ti  = индекс открытых файлов
      // Подсветка файла в древе как открытого 
      function fwe_lightfiletree(file) {
        $('a[dpath="' + file + '"]').addClass('opened_file');
      }

      function fwe_unlightfiletree(file) {
        $('a[dpath="' + file + '"]').removeClass('opened_file');
      }
      // Подсветка файла как измененного 
      function fw_editor_unsave_light(ti, file) {
        $('a[dpath="' + file + '"]').addClass('opened_file_unsave');
        $('#fwe_hi_' + ti).addClass('opened_file_unsave');
      }
      // Подсветка файла как измененного 
      function fw_editor_save_light(ti, file) {
        $('a[dpath="' + file + '"]').removeClass('opened_file_unsave');
        $('#fwe_hi_' + ti).removeClass('opened_file_unsave');
      }
      // Добавление вкладки
      function fwe_addtab(file, caption = '') {
        if (url_tabsarray[file]) {
          fwe_seltab(url_tabsarray[file]); // Выделяем вкладку с открутым файлом
          // if(!confirm('Файл уже открыт ! Повторное открытие приведет к потере всех не сохраненных данных.')){	return false;	}
          // else{ fwe_lightfiletree(file); return  url_tabsarray[file]; }
          return false;
        }
        // найти вкладку с таким url + Предложить перезагрузить!
        var tab = new fwetab(); // синтаксис "конструктор объекта"
        tab.file = file;
        var tix = Math.max.apply(null, Object.keys(tabsarray)); //  максимальный индекс вкладок
        if (tix == '-Infinity') {
          tix = 0;
        }
        // console.log( tix );
        var ti = tix + 1;
        tabsarray[ti] = tab;
        url_tabsarray[file] = ti; // [file] = ti
        //  print_r( url_tabsarray );
        $('<div class="fwe_tabhead_link" id="fwe_hi_' + ti + '" data-ti="' + ti + '" >' + caption + '<div class="tabclose" data-ti="' + ti + '">x</div>').appendTo('#tabs_headers');
        $('<div class="fwe_tabbody" id="fwe_bi_' + ti + '" data-ti="' + ti + '" ><form id="fwe_form_' + ti + '"><input type="text" id="fwe_bi_urlf_' + ti + '" class="finput" name="finput"  /><div style="width:100%;  " name="content" class="fwe_data" id="fwe_bi_dataf_' + ti + '"></d></form></div>').appendTo('#tabs_body');
        $('#fwe_bi_urlf_' + ti).val(file); // Поле с file
        // $('#fwe_bi_dataf_'+ti).text('123123123'); // Поле с file
        // ДЕлаем активной новую
        fwe_seltab(ti);
        fwe_lightfiletree(file);
        return ti;
      }
      // Закрытие вкладки  
      function closetab(ti) {
        // tabclose
        ///!!!!!!!!!!!!! ПРОВЕРЯТЬ UNSAVE и выводить confirm
        $('#fwe_hi_' + ti).hide(100); // Удаляем заголовок вкладки
        // задержка чтобы отобразилась анимация
        setTimeout(function() {
          $('#fwe_hi_' + ti).remove(); // Удаляем заголовок вкладки
          $('#fwe_bi_' + ti).remove(); // Удаляем тело вкладки 
          var file = tabsarray[ti].file;
          fwe_unlightfiletree(file);
          delete url_tabsarray[file]; // удаляем из индекса открытых  
          delete tabsarray[ti]; // удаляем редактор
        }, 100);
        //Переключиться на вкладку предидущую
        if (tabsarray[ti - 1]) {
          //fwe_seltab(ti-1);
        } else {
          if (tabsarray[ti + 1]) {
            //fwe_seltab(ti+1);
          } else {
            //
            //alert('Нет предидущего и следующего');
          }
        }
      }
      // переход на вкладку (смена активной вкладки по индексу)
      function fwe_seltab(ti) {
        $('.fwe_tabhead_link').removeClass('fwe_tabhead_link_active');
        $('#fwe_hi_' + ti).addClass('fwe_tabhead_link_active');
        $('.fwe_tabbody').hide(); // Скрываем неактивные
        $('#fwe_bi_' + ti).show(); // Показываем вкладку
        global_ti = ti; // Пишем индекс текущей вкладки в глоб перменную
      }
      // Клик по загаловку вкладки 
      $(document).on('click', '.fwe_tabhead_link', function() {
        var ti = $(this).attr('data-ti');
        fwe_seltab(ti);
        return false;
      });
      // Клик по крестику (закрытие) вкладки 
      $(document).on('click', '.tabclose', function() {
        var ti = $(this).attr('data-ti');
        closetab(ti);
        return false;
      });
      // Закрыть все
      $("#fwe_closeall").click(function() {
        var all_ti = Object.keys(tabsarray);
        // print_r(all_ti);
        all_ti.forEach(function(i) {
          closetab(i);
        });
        return false;
      });
      // Редактирование кода (Поле изменено текущую вкладку помечаем как не сохраненную)
      $(document).on('change', '.fwe_data', function() {
        $('#fwe_hi_' + global_ti).css('color', 'red');
        return false;
      });
      // ЗАГРУЗКА ФАЙЛА
      function loadfileeditor(url, file, toid, ti) {
        $("#loader").show();
        // вначале грузить файл в jsoon опредлеять datatype и разрешение и в зависимости от него грузить обработчик
        /*
        1 - Редактор 
        2 - просмотрщик директорий (картинки делает миниатюрами ) позволяет заливать и удалять файлы перименовывать итп + пертаскивать в дерево ?
        */
        tabsarray[ti].editor = generate_editor2(toid); // Сохраняем редкатор в массив открытых вкладок 
        $("#loader").show();
        $.ajax({
          type: 'GET',
          dataType: 'text',
          async: false,
          url: url,
          // shows the loader element before sending.
          beforeSend: function() {
            tabsarray[ti].editor.setValue(" ... Загрузка файла");
            addlog(' Загрузка файла: ' + file, 1);
            $("#loader").show();
          },
          success: function(data) {
            var json = $.parseJSON(data);
            //print_r(json);
            // codemiror	    
            tabsarray[ti].editor.setValue(json.data);
            tabsarray[ti].editor.focus(1); // Переводим курсор 
            tabsarray[ti].editor.gotoLine(1, 0); // СТавим курсор на позицию
            var modelist = ace.require("ace/ext/modelist");
            var mode = modelist.getModeForPath(file); //mode
            print_r(mode.mode);
            tabsarray[ti].editor.session.setMode(mode.mode);
            // Редактирование кода ПОСЛЕ ЗАГРУЗКИ (Поле изменено текущую вкладку помечаем как не сохраненную)
            tabsarray[ti].editor.on('change', function() {
              fw_editor_unsave_light(ti, file);
            });
            // $(toid).text(json.data);
            //tabsarray[ti].editor.save();
          },
          complete: function() {
            $("#loader").hide();
            $('#f').val(file);
            addlog('Загружен файл: ' + file);
          }
        });
      }
      // Сохранение файла ! 
      function fwe_save(ti = '') {
        if (!ti) {
          ti = $('.fwe_tabhead_link_active').attr('data-ti');
        }
        $("#loader").show();
        // tabsarray[ti].editor.save(); // Обновляем текстереа
        var file = url_tabsarray[ti];
        var file = tabsarray[ti].file;
        var form = $('#fwe_form_' + ti);
        // ajax запрос
        var dataform = form.serialize();
        //print_r(dataform);
        $.ajax({
          type: 'POST',
          data: dataform,
          async: false,
          url: 'index.php?ajax=1&act=savefile&f=' + file,
          // shows the loader element before sending.
          beforeSend: function() {
            addlog(' Сохранение файла: ' + file, 1);
            $("#loader").show();
          },
          success: function(data) {
            var json = $.parseJSON(data);
            //tabsarray[ti].editor.save();
          },
          complete: function() {
            $("#loader").hide();
            $('#f').val(file);
            addlog('Сохранен файл: ' + file);
          }
        });
        fw_editor_save_light(ti, file);
        addlog('Сохранен файл: ' + file);
        return false;
      }
      // Получить выделенный текст 
      function getSelectedRange() {
        ti = $('.fwe_tabhead_link_active').attr('data-ti');
        return {
          from: tabsarray[ti].editor.getCursor(true),
          to: tabsarray[ti].editor.getCursor(false)
        };
      }

      function autoFormatSelection() {
        ti = $('.fwe_tabhead_link_active').attr('data-ti');
        var range = getSelectedRange();
        tabsarray[ti].editor.autoFormatRange(range.from, range.to);
        return false;
      }
      $("#autoFormatSelection").click(function() {
        autoFormatSelection();
        return false;
      });

      function commentSelection(isComment) {
        ti = $('.fwe_tabhead_link_active').attr('data-ti');
        var range = getSelectedRange();
        tabsarray[ti].editor.commentRange(isComment, range.from, range.to);
        return false;
      }
      $("#commentSelection").click(function() {
        commentSelection(true);
        return false;
      });
      // КНОПКА СОХРАНИТЬ В ПАНЕЛИ
      $("#fwe_save").click(function() {
        fwe_save();
        return false;
      });
      /* Перехват CTRL +S */
      document.onkeydown = function(e) {
        if (e.ctrlKey && e.keyCode === 83) {
          fwe_save()
          if (e.preventDefault) e.preventDefault();
          e.returnValue = false;
        }
      };
    }); // реди
    // Запрет F5 и тп
    window.onbeforeunload = function(evt) {
      evt = evt || window.event;
      evt.returnValue = "Внесенные изменения не сохранятся";
    }
  </script>
  </div>
</body>