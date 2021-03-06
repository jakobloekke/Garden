jQuery(document).ready(function($) {
   
   // Load news & tutorials from Vanilla
   var lists = $('div.Column div.List'),
      newsColumn = $('div.NewsColumn div.List'),
      helpColumn = $('div.HelpColumn div.List');

   loadFeed = function(container, type, rows, format) {
      $.ajax({
         type: "GET",
         url: gdn.url('/dashboard/utility/getfeed/'+type+'/'+rows+'/'+format+'/'),
         success: function(data) {
            container.removeClass('Loading');
            container.html(data);
         },
         error: function() {
            container.removeClass('Loading');
            container.text('Failed to load '+type+' feed.');
         }
      });
   };

   lists.addClass('Loading');
   loadFeed(newsColumn, 'news', 3, 'extended');
   loadFeed(helpColumn, 'help', 3, 'extended');
   
   // Phone-home to the VanillaForums server to check for updates
   var updateChecks = gdn.definition('UpdateChecks', '');
   if (updateChecks != '') {
      var webroot = gdn.definition('WebRoot', '');
      var data = 'source='+webroot
         + '&users=' + gdn.definition('CountUsers', 0)
         + '&conversations=' + gdn.definition('CountConversations', 0)
         + '&messages=' + gdn.definition('CountConversationMessages', 0)
         + '&discussions=' + gdn.definition('CountDiscussions', 0)
         + '&comments=' + gdn.definition('CountComments', 0)
         + '&updateChecks=' + updateChecks;

      $.ajax({
         type: "POST",
         url: gdn.url('/dashboard/utility/updateproxy'),
         data: data,
         dataType: 'json',
         success: function(json) {
            if (json.messages != '' || json.response != '') {
               // Save the message
               $.ajax({
                  type: "POST",
                  url: gdn.url('/dashboard/utility/updateresponse'),
                  data: 'Messages='+json.messages+'&Response='+json.response+'&TransientKey='+gdn.definition('TransientKey'),
                  success: function() {
                     // After the responses have been saved, re-fill the
                     // #Content with this page's view (in case there are any
                     // messages to be displayed)
                     if (json.messages != '')
                        $('#Content').load(
                           gdn.url('/dashboard/settings/index'),
                           'DeliveryType=ASSET&DeliveryMethod=XHTML'
                        );
                  }
               });
            }
         }
      });
   }
});