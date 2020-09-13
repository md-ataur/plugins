(function ($) {
  $(document).ready(function ($) {

    // Main configuration
    var wpfd_configuration = {
      status: '',
      init: function () {
        $(document).on('change', '.ju-switch-button .switch input[type="checkbox"]', this.switch);
        $(document).on('change', '#search_config .switch input[type="checkbox"]:not(input[name="ref_plain_text_search"]), #search_config select', this.search_shortcode);
        $(document).on('change', '#upload_cattegory_id', this.upload_shortcode);
        $(document).on('change input', '#ref_statistics_storage_times, #ref_statistics_storage_duration', this.statisticsStorageChanged)
        // $(document).on('click', '.shortcode-copy', this.copy);
        $(document).on('click', '.ju-toggle', this.toggle);
        $(document).on('input', '.ju-role-search-input', this.search_roles);
        this.loadStatisticsStorageValue();
        $('#ref_exclude_category_id').chosen({width: 'auto'});
        this.initQtip();
      },
      statisticsStorageChanged: function(e) {
        var $this = $(this);
        var ref = $this.attr('name').replace('ref_', '');
        $('input[name="' + ref + '"]').val($this.val());
      },
      loadStatisticsStorageValue: function() {
        var statistics_storage_times = $('input[name="statistics_storage_times"]').val();
        var statistics_storage_duration = $('input[name="statistics_storage_duration"]').val() || 'forever';
        $('#ref_statistics_storage_times').val(statistics_storage_times);
        $('#ref_statistics_storage_duration').val(statistics_storage_duration);
      },
      switch: function (e) {
        var $this = $(e.target);
        var ref = $this.attr('name').replace('ref_', '');
        $('input[name="' + ref + '"]').val($this.prop('checked') ? 1 : 0);
        if (ref === 'show_categories') {
          $('input[name="' + ref + '"]').trigger('change');
        }
      },
      search_shortcode: function () {
        var cat = $('#cat_filter'),
          tag = $('#tag_filter'),
          display_tag = $('#display_tag'),
          create_filter = $('#create_filter'),
          update_filter = $('#update_filter'),
          file_per_page = $('#file_per_page'),
          search_category_id = $('#search_category_id'),
          ref_exclude_category_id = $('#ref_exclude_category_id'),
          search_shortcode_input = $('#shortcode_value');
        var catId = '';
        if (search_category_id.length > 0 && search_category_id.val() !== '') {
          catId = ' catid="' + search_category_id.val() + '"';
        }

        var excludeIds = '';
        if (ref_exclude_category_id.length > 0 && ref_exclude_category_id.val() !== '') {
          if (ref_exclude_category_id.val() !== null) {
            excludeIds = ' exclude="' + ref_exclude_category_id.val().join(',') + '"';
            $('#exclude_category_id').val(ref_exclude_category_id.val().join(','));
          } else {
            $('#exclude_category_id').val('');
          }
        }

        var shortcode = '[wpfd_search' + catId + excludeIds +' cat_filter="' + cat.val() + '" tag_filter="' + tag.val() + '" display_tag="' + display_tag.val() + '" create_filter="' + create_filter.val() + '" update_filter="' + update_filter.val() + '" file_per_page="' + file_per_page.val() + '"]';
        $(search_shortcode_input).val(shortcode);
      },
      upload_shortcode: function () {
        var upload_shortcode_value = $('#upload_shortcode'),
          upload_cattegory_id = $('#upload_cattegory_id');
        var shortcode_upload = '[wpfd_upload category_id="' + upload_cattegory_id.val() + '"]';
        upload_shortcode_value.val(shortcode_upload);
      },
      copy: function (e) {
        e.stopPropagation();
        var $this = $(this);

        var inputId = $this.data('ref');
        var linkcopy = $('input[name="' + inputId + '"]').val();

        var inputlink = document.createElement("input");
        inputlink.setAttribute("value", linkcopy);
        document.body.appendChild(inputlink);
        inputlink.select();
        document.execCommand("copy");
        document.body.removeChild(inputlink);
        $.gritter.add({text: wpfd_admin.msg_shortcode_copied_to_clipboard});
        $this.unbind('click');
      },
      toggle: function (e) {
        var $this = $(e.target);
        $this.toggleClass('collapsed');
        $this.next('.ju-settings-option-group').slideToggle();
      },
      search_roles: function (e) {
        var $this = $(e.target);
        var searchKey = $this.val().trim().toLowerCase();
        $('.ju-right-panel .ju-heading.ju-toggle').show();
        $('.ju-right-panel .ju-heading.ju-toggle + .ju-settings-option-group').show();
        if (searchKey === '') {
          return false;
        }
        // Hide everything
        $('.ju-right-panel .ju-heading.ju-toggle').hide();
        $('.ju-right-panel .ju-heading.ju-toggle + .ju-settings-option-group').hide();
        // We are search on role name only
        $('.ju-right-panel .ju-heading.ju-toggle:contains("' + searchKey + '")').show();
        $('.ju-right-panel .ju-heading.ju-toggle:contains("' + searchKey + '") + .ju-settings-option-group').show();

      },
      initQtip: function () {
        $('.ju-setting-label').qtip({
          content: {
            attr: 'title',
          },
          position: {
            my: 'top left',
            at: 'bottom left',
          },
          style: {
            tip: {
              corner: true,
            },
            classes: 'wpfd-qtip qtip-rounded wpfd-qtip-dashboard',
          },
          show: 'hover',
          hide: {
            fixed: true,
            delay: 10,
          },

        });
      },
    };

    // Search indexer
    var wpfd_indexer = {
      init: function () {
        $(document).on('change', '#search_config .switch input[name="ref_plain_text_search"]', this.onChange);
        $(document).on('mouseover', '#wpfd_rebuild_search_index', this.onMouseOver);
        $(document).on('mouseout', '#wpfd_rebuild_search_index', this.onMouseOut);
        $(document).on('click', '#wpfd_rebuild_search_index', this.run);

        this.onReady();
      },
      onReady: function () {
        var $elem = $('#plain_text_search');
        if ($elem.length && parseInt($elem.val()) === 1) {
          wpfd_indexer.pingTimer();
        }
      },
      onMouseOver: function (e) {
        e.preventDefault();
        var $this = $(e.target);
        this.status = $this.html();
        $this.html('Build Search Index');

        return false;
      },
      onMouseOut: function (e) {
        e.preventDefault();
        var $this = $(e.target);
        $this.html(this.status);

        return false;
      },
      onChange: function (e) {
        var $this = $(e.target);
        var $indexerContainer = $('.wpfd-search-indexer');
        $indexerContainer.slideToggle();
      },
      run: function (e) {
        e.preventDefault();
        var $this = $(e.target);
        var confirm_text = $this.attr('data-confirm');
        var isallow = false;

        if ((confirm_text) && (confirm_text.length > 0)) {
          if (confirm(confirm_text)) {
            isallow = true;
          }
        } else {
          isallow = true;
        }

        if (isallow) {
          wpfd_indexer.ftsAction('fts.submitrebuild', {pid: wpfd_fts.pid}, function (response) {
            //
          });
        }

        return false;
      },
      pingTimer: function () {
        wpfd_indexer.ftsAction('fts.ajaxping', {'pid': wpfd_fts.pid}, wpfd_indexer.pingProgressor);
      },
      indexerBuildStatus: function (status) {
        status = JSON.parse(status);
        if (!status.message) {
          if (parseInt(status.index_ready) === 1) {
            $("#wpfd_rebuild_search_index").css('background', 'linear-gradient(90deg, #5dca70 100%, #2196f3 100%)');
            this.status = "<span class=\"wpfd_fts_status_bullet wpfd_fts_white\">&#10003;</span>"
              + "Index ready! On index: <b>" + status.n_inindex + "</b> files";
            $('#wpfd_rebuild_search_index').html(this.status);
          } else {
            if (status.n_pending) {
              // $('#indexResult .progress').removeClass("hide");
              if (status.n_inindex === 0) {
                $('#wpfd_rebuild_search_index').html('<i class="wpfd-icon-indexing"></i>  Prepare to index ' + status.n_pending + ' files');
              } else {
                var total = status.n_inindex + status.n_pending;
                var processerStatus = '<i class="wpfd-icon-indexing"></i> Indexer is running: ' + status.n_actual + ' / ' + total + ' files';
                var percent = status.n_actual * 100 / (status.n_inindex + status.n_pending);
                $("#wpfd_rebuild_search_index").css('background', 'linear-gradient(90deg, #5dca70 ' + percent + '%, #2196f3 ' + percent + '%)');
                $('#wpfd_rebuild_search_index').html(processerStatus);
              }
            } else if (status.n_pending === 0) {
              $("#wpfd_rebuild_search_index").css('background', 'linear-gradient(90deg, #5dca70 0%, #2196f3 0%)');
              $("#wpfd_rebuild_search_index").html('Rebuild search index');
            } else {
              console.log(status);
            }
          }
        } else {
          this.status = "<span class=\"wpfd_fts_status_bullet wpfd_fts_red\">&#9679;</span>"
            + "<b>" + status.message + "</b>";
          $('#wpfd_rebuild_search_index').html(this.status);
        }
      },
      pingProgressor: function (response) {
        if (('code' in response) && (response['code'] === 0)) {
          wpfd_indexer.indexerBuildStatus(response['status']);
          var result = response['result'];
          switch (result) {
            case 5:
              // Start indexing of part
              wpfd_indexer.ftsAction('fts.rebuildstep', {'pid': wpfd_fts.pid}, wpfd_indexer.pingProgressor);
              break;
            case 10:
              // Indexing in progress (other process)
              setTimeout(wpfd_indexer.pingTimer, wpfd_fts.pingtimeout);
              break;
            case 0:
            default:
              // Nothing to index
              setTimeout(wpfd_indexer.pingTimer, wpfd_fts.pingtimeout);
          }
        }
      },
      ftsAction: function (action, data, callback) {
        var url = wpfdajaxurl;
        if (url.indexOf('wpfd') === -1) {
          url = wpfdajaxurl + "?action=wpfd&"
        }
        $.ajax({
          url: url + "task=" + action,
          method: 'POST',
          data: {'__xr': 1, 'z': JSON.stringify(data)},
          success: function (response) {
            var ret = true;
            if ((typeof callback !== 'undefined') && (callback)) {
              var vars = {};
              for (var i = 0; i < response.length; i++) {
                switch (response[i][0]) {
                  case 'vr':
                    vars[response[i][1]] = response[i][2];
                    break;
                }
              }
              ret = callback(vars);
            }
            if ((ret) || (typeof ret === 'undefined')) {
              for (var i = 0; i < response.length; i++) {
                var data = response[i];
                switch (data[0]) {
                  case 'cn':
                    break;
                  case 'al':
                    alert(data[1]);
                    break;
                  case 'as':
                    if ($(data[1]).length > 0) {
                      $(data[1]).html(data[2]);
                    }
                    break;
                  case 'js':
                    eval(data[1]);
                    break;
                  case 'rd':
                    document.location.href(data[1]);
                    break;
                  case 'rl':
                    window.location.reload();
                    break;
                }
              }
            }
          },
          error: function () {
            window.location.reload();
          },
          dataType: 'json',
        });

      },
    };

    // Remember activate tab
    var wpfd_tabs = {
      init: function () {
        $(document).on('click', '.ju-menu-tabs > .tab > a,.ju-top-tabs > .tab > a', this.tabClick);
        // $(document).on('click', '.ju-top-tabs > .tab > a', this.subTabClick);
        $(document).on('ready', this.activateTabFromCookie);
      },
      tabClick: function (e) {
        var $this = $(e.target);
        var tab_id = $this.attr('href').replace('#', '');
        wpfd_tabs.setActivatedTabToCookie(tab_id);
      },
      subTabClick: function (e) {
        var $this = $(e.target);
        var tab_id = $this.attr('href').replace('#', '');
        wpfd_tabs.setActivatedTabToCookie(tab_id);
      },
      activateTabFromCookie: function () {
        var active_tab = wpfd_tabs.getActivatedTabFromCookie();
        if (active_tab !== '') {
          var tab = $(".ju-menu-tabs a[href='#" + active_tab + "']");
          if (tab.length) {
            tab.trigger('click');
          } else { // This is sub tab
            tab = $(".ju-top-tabs a[href='#" + active_tab + "']");
            var parentHref = $(tab).closest('.ju-content-wrapper').attr('id');
            var tabHref = $(tab).attr('href').replace('#', '');
            $('.ju-menu-tabs .tab a.link-tab').removeClass('expanded active');
            var parentNode = $('.ju-menu-tabs .tab a.link-tab[href="#' + parentHref + '"]');
            $(parentNode).trigger('click');
            $(parentNode).closest('li.tab').find('.ju-submenu-tabs').find('div.link-tab[data-href="#' + tabHref + '"]').trigger('click');
          }
        }
      },
      getActivatedTabFromCookie: function () {
        var name = "wpfd_config_activated_tab=";
        var ca = document.cookie.split(';');
        for (var i = 0; i < ca.length; i++) {
          var c = ca[i];
          while (c.charAt(0) === ' ') c = c.substring(1);
          if (c.indexOf(name) === 0) return c.substring(name.length, c.length);
        }
        return '';
      },
      setActivatedTabToCookie: function (id) {
        document.cookie = 'wpfd_config_activated_tab=' + id;
      },
    };

    // Init
    wpfd_configuration.init();
    wpfd_indexer.init();
    wpfd_tabs.init();

    // remove message
    if(jQuery('.save-message').length > 0) {
      var $ =jQuery;
      $('.cancel-btn').on('click', function () {
        $('.save-message').remove();
      });
    }
  });
})(jQuery);