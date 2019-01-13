$.fn.modal.Constructor.prototype.enforceFocus = function() {};

d_shopunity = {

    setting: {
        'purchase_url' : '', //set admin url with token
    },

    init: function(setting){
        this.setting = $.extend({}, this.setting, setting);
        this.render();
    },



    popupStart: function($title){
        $("#modal").modal('show');
        $("#modal .preloader").show();
        $("#modal .modal-title").html($title);
        $("#modal .modal-body").html('');
    },

    popupShow: function($content){
        $("#modal .preloader").hide();
        $('#modal .modal-body').html($content);
    },

    popupPurchase: function($node){

        this.popupStart('Purchase extension');
        var that = this;
        var extension_recurring_price_id = $node.closest('select').val()
        $.ajax({
            url: $node.data('href')+'&extension_recurring_price_id='+extension_recurring_price_id,
            dataType: 'json',
            method: 'get',
            success: function(json) {
                
                if(json['content']){
                    that.popupShow(json['content']);
                }
            }
        }); 

        return false;
    },

    popupPayInvoice: function($node){

        this.popupStart('Pay invoice');
        var that = this;
        $.ajax({
            url: $node.data('href'),
            dataType: 'json',
            method: 'get',
            success: function(json) {
                
                if(json['content']){
                    that.popupShow(json['content']);
                }
            }
        }); 

        return false;
    },

    popup: function($node){

        this.popupStart('Install extension');
        var that = this;
        $.ajax({
            url: $node.data('href'),
            dataType: 'json',
            method: 'get',
            success: function(json) {
                
                if(json['content']){
                    that.popupShow(json['content']);
                    var mbooth = JSON.stringify(JSON.parse($("#modal").find(".mbooth").html()), null, 2);
                    $("#modal").find(".mbooth").html(syntaxHighlight(mbooth));
                }
            }
        }); 

        return false;

    },

    purchaseExtension: function(extension_id, extension_recurring_price_id){
        var href = this.setting.purchase_url;
            that = this;

        swal({  
            title: "Pusrchase this Extension",
            text:'You are about to purchase this extension',
            showCancelButton: true, 
            confirmButtonColor: "#5bc0de",  
            confirmButtonText: "Yes, Purchase it!", 
            closeOnConfirm: false,
            closeOnCancel: true,
            showLoaderOnConfirm: true
        }, 
        function(isConfirm){  
            if (isConfirm) {    
                href += '&extension_id='+extension_id+'&extension_recurring_price_id='+extension_recurring_price_id;
                location.href = href;
            } else {     
                that.hideLoading($('.loading'));
            }   
        });

        return false;

        
    },

    claimExternalOrder: function($node){
        var that = this;
        swal({  
            title: "Find your External order", 
            text: "<p>If you have a license from Opencart or any other regestered External Marketplace, you can claim it in Shopuniy. Provide your External Market Email and Order Id (Purchase Id)</p><br/><form id=\"claim_external_order\" class=\"form\"><select class=\"form-control show\"  name=\"market\" id=\"market\"><option value=\"opencart\">Opencart.com</option><option value=\"isenselabs\">Isenselabs.com</option><option value=\"dreamvention\">Dreamvention.ee</option></select><input class=\"form-control show\" placeholder=\"E-mail\" name=\"user_id\" id=\"user_id\" type=\"email\" /><input class=\"form-control show\" placeholder=\"Order Id\" name=\"order_id\" id=\"order_id\" type=\"text\" /></form>",
            html: true,
            showCancelButton: true, 
            confirmButtonColor: "#8fbb6c",  
            confirmButtonText: "Find my License!",   
            closeOnConfirm: false,
            closeOnCancel: true,
            showLoaderOnConfirm: true
        }, 
        function(isConfirm){  
            if (isConfirm) { 
                $.ajax({
                    url: $node.data('href'),
                    dataType: 'json',
                    method: 'post',
                    data:  $('form#claim_external_order').serialize(),
                    success: function(json) {
                        if(json['success']){
                            swal({  
                                title: "Voucher for licnese found",   
                                text: json['text'], 
                                type: "success",    
                                showCancelButton: true, 
                                confirmButtonColor: "#AEDEF4",  
                                confirmButtonText: "Apply voucher",  
                                closeOnConfirm: false,
                                closeOnCancel: true,
                                showLoaderOnConfirm: true
                            },
                            function(isConfirm){  
                                if (isConfirm) {
                                    location.href = json['apply'];
                                } else {     
                                    that.hideLoading($('.loading'));
                                }   
                            });
                        }else if(json['errors']){
                            
                            swal("Oops!", json['errors'][0]['message'], "error");
                        }else if(json['error']){
                            
                            swal("Oops!", json['error'], "error");
                        }

                    }
                });   
            } else {     
                that.hideLoading($('.loading'));
            }   
        });

        return false;
        
    },

    installExtension: function($node){
        var that = this;
        var codename = $node.data('codename');
        var $log = $('#extension_popup_'+codename+' .log');
        var $popup = $('#extension_popup_'+codename);
        
        $popup
            .find('.text-start').addClass('hide').parent()
            .find('.text-process').removeClass('hide');
        
        if (!!window.EventSource) {
            var source = new EventSource( $node.data('href'));
            console.log('source');
        }

        //listen to message
        source.addEventListener('message', function(e) {

            var data = JSON.parse(e.data);
            $log.append(data.message+"\n");
           
            if(data.installed){
                $('#required_'+data.installed)
                    .find('.text-process').addClass('hide').parent()
                    .find('.text-complete').removeClass('hide');

                if(data.thumb){
                    $('#extension_thumb_'+data.installed).replaceWith(data.thumb);
                }
           }
           if(data.activate){
                that.activateExtension($('#extension_thumb_'+data.activate).find('.activate-extension'));
                that.activateExtension($('#required_'+data.activate).find('.activate-extension'));  
           }
           
        }, false);

        //listen to open
        source.addEventListener('open', function(e) {
          that.showLoading($('#extension_thumb_'+codename).find('.loading'));
        }, false);

        //listen to error
        source.addEventListener('error', function(e) {
            $log.append('Connection closed');
            that.hideLoading($('.loading'));
            source.close();

            $popup
                .find('.text-process').addClass('hide').parent()
                .find('.text-complete').removeClass('hide');
          
        }, false);
        
        return false;
    },

    activateExtension: function($node){
        $.ajax({
            url: $node.data('href'),
            dataType: 'json',
            method: 'get',
            success: function(json) {
                console.log(json)
            }
        });
    },

    deactivateExtension: function($node){
        $.ajax({
            url: $node.data('href'),
            dataType: 'json',
            method: 'get',
            success: function(json) {
                console.log(json)
            }
        });
    },

    updateExtension: function($node){
        var that = this;
        swal({  
            title: "Update this Extension", 
            text: "You are about to update this extension!",    
            type: "info",   
            showCancelButton: true, 
            confirmButtonColor: "#8fbb6c",  
            confirmButtonText: "Yes, Update it!",   
            closeOnConfirm: false,
            closeOnCancel: true,
            showLoaderOnConfirm: true
        }, 
        function(isConfirm){  
            if (isConfirm) {     
                $.ajax({
                    url: $node.data('href'),
                    dataType: 'json',
                    method: 'get',
                    success: function(json) {
                        if(json['installed']){
                            swal({  
                                title: "Updated",   
                                text: json['text'], 
                                type: "success",    
                                showCancelButton: true, 
                                confirmButtonColor: "#AEDEF4",  
                                confirmButtonText: "View",  
                                closeOnConfirm: false,
                                closeOnCancel: true,
                                showLoaderOnConfirm: true
                            },
                            function(isConfirm){  
                                if (isConfirm) {
                                    location.href = json['view'];
                                } else {     
                                    that.hideLoading($('.loading'));
                                }   
                            });
                        }

                        if(json['extension']){
                            $('#extension_thumb_'+json['codename']).replaceWith(json['extension']);
                        }
                    }
                });   
            } else {     
                that.hideLoading($('.loading'));
            }   
        });

        return false;
        
    },

    downloadExtension: function($node){

        location.href = $node.data('href');
    },

    deleteExtension: function($node){
        var that = this;
        swal({  
            title: "Delete this Extension", 
            text: "You are about to delete this extension!",    
            type: "warning",    
            showCancelButton: true, 
            confirmButtonColor: "#f56b6b",  
            confirmButtonText: "Yes, Delete it!",   
            closeOnConfirm: false,
            closeOnCancel: true,
            showLoaderOnConfirm: true
        }, 
        function(isConfirm){  
            if (isConfirm) {
                that.deactivateExtension($node.closest('.deactivate-extension'));

                $.ajax({
                    url: $node.data('href'),
                    dataType: 'json',
                    method: 'get',
                    success: function(json) {
                        if(json['uninstalled']){
                            swal({  
                                title: "Uninstalled",   
                                text: json['text'], 
                                type: "success",    
                                showCancelButton: true, 
                                confirmButtonColor: "#AEDEF4",  
                                confirmButtonText: "View",  
                                closeOnConfirm: false,
                                closeOnCancel: true
                            },
                            function(isConfirm){  
                                if (isConfirm) {
                                    location.href = json['view'];
                                } else {     
                                    that.hideLoading($('.loading'));
                                }   
                            });
                        }

                        if(json['extension']){
                            $('#extension_thumb_'+json['codename']).replaceWith(json['extension']);
                        }
                    }
                });  
            } else {     
                that.hideLoading($('.loading'));
            }   
        });

        return false;
        
    },

    suspendExtension: function($node){
        var that = this;
        swal({  
            title: "Cancel purchase of this Extension", 
            text: "You are about to Cancel purchase of this extension!",    
            type: "warning",    
            showCancelButton: true, 
            confirmButtonColor: "#f56b6b",  
            confirmButtonText: "Yes, Cancel this purchase!",    
            closeOnConfirm: false,
            closeOnCancel: true,
            showLoaderOnConfirm: true
        }, 
        function(isConfirm){  
            if (isConfirm) {     
                location.href = $node.data('href');  
            } else {     
                that.hideLoading($('.loading'));
            }   
        });

        return false;
        
    },

    submitExtension: function($node){
        var that = this;
        swal({  
            title: "Submit extension to shopunity.net", 
            text: "You are about to start a submission process of this extension!", 
            type: "warning",    
            showCancelButton: true, 
            confirmButtonColor: "#f56b6b",  
            confirmButtonText: "Yes, submit this extension!",   
            closeOnConfirm: false,
            closeOnCancel: true,
            showLoaderOnConfirm: true
        }, 
        function(isConfirm){  
            if (isConfirm) {     
                location.href = $node.data('href');  
            } else {     
                that.hideLoading($('.loading'));
            }   
        });

        return false;
        
    },

    approveExtension: function($node){
        var that = this;
        swal({  
            title: "Approve this extension",    
            text: "You are about to approve this extension!",   
            type: "warning",    
            showCancelButton: true, 
            confirmButtonColor: "#f56b6b",  
            confirmButtonText: "Yes, approve this extension!",  
            closeOnConfirm: false,
            closeOnCancel: true,
            showLoaderOnConfirm: true
        }, 
        function(isConfirm){  
            if (isConfirm) {     
                location.href = $node.data('href');  
            } else {     
                that.hideLoading($('.loading'));
            }   
        });

        return false;
        
    },

    disapproveExtension: function($node){

        var that = this;
        swal({  
            title: "Disapprove this extension", 
            text: "You are about to disapprove this extension!",    
            type: "input",  
            inputPlaceholder: "Tester comment",
            showCancelButton: true, 
            confirmButtonColor: "#f56b6b",  
            confirmButtonText: "Yes, disapprove this extension!",   
            closeOnConfirm: false,
            closeOnCancel: true,
            showLoaderOnConfirm: true
        }, 
        function(inputValue){   
            if (inputValue === false) return false;      
            if (inputValue === "") {     
                swal.showInputError("You need to write something!");     
                return false 
            }      

            $.ajax({
                url: $node.data('href'),
                dataType: 'html',
                data: 'tester_comment='+inputValue,
                method: 'post',
                success: function(html) {
                    console.log(html)
                    location.reload();
                }
            });
        });
    

        return false;

    },

    showExtensionJson: function($node){

        this.popupStart('Extension Json');
        var that = this;
        $.ajax({
            url: $node.data('href'),
            dataType: 'json',
            success: function(json) {
                $("#modal .preloader").hide();
                $("#modal").find(".modal-body").html('<pre>'+syntaxHighlight(json)+'</pre>');
    
            }
        });
        
    },

    developerUpdateExtension: function($node){
        var that = this;
        swal({  
            title: "Update extension in all shopunity.net", 
            text: "You are about to start an update of all installations of this extension throughout shopunity! Are you sure?",    
            type: "warning",    
            showCancelButton: true, 
            confirmButtonColor: "#f56b6b",  
            confirmButtonText: "Yes, update all installations!",    
            closeOnConfirm: false,
            closeOnCancel: true,
            showLoaderOnConfirm: true
        }, 
        function(isConfirm){  
            if (isConfirm) {     
                $.ajax({
                    url: $node.data('href'),
                    dataType: 'json',
                    method: 'get',
                    success: function(json) {
                        that.hideLoading($('.loading'));

                        if(json['success']){
                            var text = json['success'];
                            var type = "success";
                        }

                        if(json['error']){
                            var text = json['error'];
                            var type = "warning";
                        }
                        swal({  
                            title: "Updated",   
                            text: text, 
                            type: type, 
                            showCancelButton: false, 
                            confirmButtonColor: "#AEDEF4",  
                            confirmButtonText: "Ok",    
                            closeOnConfirm: false,
                            closeOnCancel: true,
                            showLoaderOnConfirm: true
                        });
                    }
                });  
            } else {     
                that.hideLoading($('.loading'));
            }   
        });

        return false;
    },

    showLoading: function($loading){
        $loading.addClass('show');
    },

    hideLoading: function($loading){
        $loading.removeClass('show');
    },

    search: function($node){
        location.href = $node.data('href') + '&search=' + $('input[name=search]').val();
    },

    render: function(){
        var that = this;

        //content_top.tpl
        //Stack menu when collapsed
        $('#bs-example-navbar-collapse-1').on('show.bs.collapse', function() {
            $('.nav-pills').addClass('nav-stacked');
            $('.logout').removeClass('pull-right');
        });

        $(document).on('click', '.popup-purchase', function(){
            that.popupPurchase($(this));
        });

        $(document).on('click', '.popup-pay-invoice', function(){
            that.popupPayInvoice($(this));
        });

        $(document).on('click', '.popup-extension', function(){
            that.popup($(this));
        });

        $(document).on('click', '.purchase-extension .btn', function(){
            that.purchaseExtension($(this).data('extension-id'), $(this).parents('.purchase-extension').find('select').val());
        });

        $(document).on('click', '.claim-external-order', function(){
            that.claimExternalOrder($(this));
        });

        

        $(document).on('click', '.install-extension', function(){
            that.installExtension($(this));
        });

        // $(document).on('click', '.test-extension', function(){
        //  that.installExtension($(this));
        // });

        $(document).on('click', '.download-extension', function(){
            that.downloadExtension($(this));
        });

        // $(document).on('click', '.update-extension', function(){
        //  that.updateExtension($(this));
        // });

        $(document).on('click', '.delete-extension', function(){
            that.deleteExtension($(this));
        });

        $(document).on('click', '.activate-extension', function(){
            that.activateExtension($(this));
        });

        $(document).on('click', '.deactivate-extension', function(){
            that.deactivateExtension($(this));
        });

        $(document).on('click', '.suspend-extension', function(){
            that.suspendExtension($(this));
        });

        $(document).on('click', '.submit-extension', function(){
            that.submitExtension($(this));
        });

        $(document).on('click', '.approve-extension', function(){
            that.approveExtension($(this));
        });

        $(document).on('click', '.disapprove-extension', function(){
            that.disapproveExtension($(this));
        });

        $(document).on('click', '.developer-update-extension', function(){
            that.developerUpdateExtension($(this));
        }); 

        $(document).on('click', '.show-loading', function(){
            that.showLoading($(this).parents('.extension-thumb').find('.loading'));
        });

        $(document).on('click', '.show-extension-json', function(){
            that.showExtensionJson($(this));
        });

        $(document).on('click', '.search', function(){
            that.search($(this));
        });
    }

};
