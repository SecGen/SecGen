(function($){
  $.fn.oneClickEdit = function(options,success){
    return this.each(function(){
      // we need to preserve white space on text areas
      $.valHooks.textarea = {
        get: function(elem) {
          return elem.value.replace(/\r?\n/g, "\r\n");
        }
      };
      //set a border to avoid shifting
      $(this).css({'border':'1px solid transparent'});
      var inputType = $(this).data('input');
      var prev_el = $(this);
      var prev_val = $(this).text();
      var styles = {};
      var id = $(this).data('id');
      var field = $(this).data('field');
      var getStyles = ['padding','margin','font','background-color','color','display','width','height','resize'];
      var className = $(this).attr('class');
      var idName = $(this).attr('id');
      if(typeof idName === 'undefined'){
        idName = randomString(17);
        jQuery(prev_el).attr('id',idName);
      }
      for(var i = 0;i < getStyles.length;i++){
        var k = getStyles[i];
        var v = $(this).css(getStyles[i]);
        if(k == 'height' && (v == '2px' || v == '0px')) {
          var fsz = $(this).css('font-size');
          var minHeight = parseInt(fsz.replace('px',''))+6;
          var minHeightString = minHeight+'px'
          styles['min-height'] = minHeightString;
          $(this).css('min-height',minHeightString);
        }
        if(k == 'width' && v == '0px') {
          v = '100%';
        }
        styles[k] = v;
      }
      if(inputType == 'select') {
        styles['width'] = 'auto';
      }
      var origStyles = styles;
      delete styles['background-color'];
      // add a border on hoover for user feedback
      $(this).on('mouseenter',function(){
        $(this).css({'border':'1px solid lightgray','border-radius': '2px','box-sizing': 'border-box'});
      });
      $(this).on('mouseleave',function(){
        $(this).css({'border':'1px solid transparent','border-radius': '2px','box-sizing': 'border-box'});
      });

      // replace element on click with textarea or input
      $(this).on('click',function(){
        prev_val = $(this).text();
        styleString = 'border-color:transparent;background-color:#fff;width:'+styles["width"]+'px;height:'+styles["height"]+';outline:none;border:1px solid #4D90FE;box-shadow:0px 0px 4px 0px #4D90FE;';
        for(var k in styles){
          styleString += k +':'+styles[k]+';';
        }
        // replace double quotes with single quotes.
        var newStyleString = styleString.replace(/\"/g,"'");
        if(typeof inputType === 'undefined' || inputType == 'textarea'){
          var newElement = '<textarea id="'+idName+'" class="'+className+'" style="'+newStyleString+'">'+prev_val+'</textarea>';
        }else if(inputType == 'input'){
          var newElement = '<input id="'+idName+'" class="'+className+'" style="'+newStyleString+'" value="'+prev_val+'">';
        } else if(inputType == 'select') {
          var selectOptions = options.selectOptions;
          var prev_select_val = getKeyByValue(selectOptions, prev_val);
          prev_val = prev_select_val
          var newElement = '<select id="'+idName+'" class="'+className+'" style="'+newStyleString+'" value="'+prev_val+'">';
          for(var k in selectOptions) {
            var v = selectOptions[k];
            newElement += '<option value="'+k+'">'+v+'</option>';
          }
          newElement += '</select>';
        }
        $(this).replaceWith(newElement);
        $('#'+idName).focus();

        if(typeof inputType === 'undefined' || inputType == 'textarea') {
          var iHeight = $('#'+idName).css('line-height');
          iHeight = parseFloat(iHeight.replace('px',''));
          $('#'+idName).keydown(function(e){
            if(e.which == 13) {
              var tHeight = iHeight + $('#'+idName).get(0).scrollHeight;
              $('#'+idName).css('height',tHeight+'px');
            }
          });
        }

        if(inputType == 'select') {
          $('#'+idName).val(prev_val);
          var mouseAction = new MouseEvent("mousedown");
          document.getElementById(idName).dispatchEvent(mouseAction);
        } else {
          //clearing the value and resetting it moves cursor to end of input cross browser solution
          $('#'+idName).val('');
          $('#'+idName).val(prev_val);
        }

        // add event listener to new element
        if(inputType == 'select') {
          var eventType = 'blur change';
        } else {
          var eventType = 'blur';
        }
        $('#'+idName).on(eventType,function(){
          var newVal = $(this).val().trim();
          // do we allow for blank values
          if(newVal == ''){
            if(typeof options.allowNull === 'undefined' || options.allowNull == false){
              newVal = prev_val;
            }
          }
          //make sure the value has changed before running anything
          if(newVal != prev_val){
            //check for custom callback
            if(typeof options.onblur !== 'undefined' && options.onblur != ''){
              var callback = options.onblur;
              callback();
            }else{
              // ajax call with success function
              var data = {id:id,field:field,value:newVal};
              // check to see if custom data attributes are being posted
              if(typeof options.data !== 'undefined'){
                Object.assign(data,options.data);
              }
              $.ajax({url:options.url,type:'POST',data:data,success:success});
            }
          }

          // replace with previous element populated with new value
          $(prev_el).attr('style','');
          if(inputType == 'select') {
            newVal = selectOptions[newVal];
          }
          $(prev_el).text(newVal);
          $(this).replaceWith(prev_el);
          $('#'+idName).css(origStyles);
          $('#'+idName).css('height','auto');
          $('#'+idName).css('white-space','pre-wrap');
          $('#'+idName).oneClickEdit = null;
          $('#'+idName).removeData();
          $('#'+idName).oneClickEdit(options,success);
        });
      });
    });
  };

  function getKeyByValue(obj, val) {
    for(var k in obj) {
      var v = obj[k];
      if(v == val) {
        return k;
      }
    }
    return false;
  }

  function randomString(length) {
    var result = '';
    var chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    for (var i = length; i > 0; --i) result += chars[Math.floor(Math.random() * chars.length)];
    return result;
  }

}(jQuery));
