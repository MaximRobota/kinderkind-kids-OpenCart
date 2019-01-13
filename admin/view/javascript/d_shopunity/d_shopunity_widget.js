d_shopunity_widget = {
    setting: {
        'http' : '',
        'class' : '.d_shopunity_widget',
        'extension_id' : '', //set admin url with token
        'action' : 'loadExtension',
        'token': ''
    },

    loadExtension: function($extension_id){
        $( this.setting.class ).hide();
        $( this.setting.class ).load( this.setting.http+"index.php?route=extension/d_shopunity/extension/show_thumb&extension_id="+$extension_id+"&user_token="+this.setting.token, function(){
            if($( this.setting.class ).find(" .extension-show-thumb").html() != undefined){
                $( this.setting.class ).show();
            }
        }.bind(this));
    },

    loadUpdate: function($extension_id){
        
        $( this.setting.class ).hide();
        $( this.setting.class ).load( this.setting.http+"index.php?route=extension/d_shopunity/extension/show_update&extension_id="+$extension_id+"&user_token="+this.setting.token, function(){
            if($( this.setting.class ).find(" .extension-show-thumb").html() != undefined){
                $( this.setting.class ).show();
            }
        }.bind(this));
    },

    init: function(setting){
        this.setting = $.extend({}, this.setting, setting);
        
        if(!this.setting.token){
            console.error('d_shopunity_widget: No token specified in setting');
        }

        if(!this.setting.extension_id){
            console.error('d_shopunity_widget: No extension_id specified in setting');
        }

        this.render();
    },

    render: function(){
        if(this.setting.action == 'loadUpdate'){
            this.loadUpdate(this.setting.extension_id);
        }else{
            this.loadExtension(this.setting.extension_id);
        }
        
    }

}