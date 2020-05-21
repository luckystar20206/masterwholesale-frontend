define([
    'prototype'
], function(){
    var MRImporter = { };
    MRImporter = Class.create({
        bucket:null,
        file:null,

        options:null,
        initialize: function(options) {
            this.bucket = options.bucket;
            this.options = options;
        },

        importFile:function(file){
            this.file = file;
            this._startImport();
        },

        proccessLog:function(response){
            if (response.log){
                $("mr_console").update(response.log);
                $("mr_console-container").scrollTop = $("mr_console").getHeight();
            }
        },

        checkImport:function(){

            var _this  = this;
            new Ajax.Request(this.options.actionUrl,{
                type:"post",
                parameters:{action:"check",bucket:this.bucket,file:this.file},
                onSuccess:function(data){
                    _this.proccessResponse(data.responseJSON);
                    if (data.responseJSON.error != 1 && data.responseJSON.finished!=1){
                        setTimeout(function(){
                            _this.checkImport();
                        },5000);
                    }else if (data.responseJSON.finished==1){
                        _this.overlay.remove();
                        alert("FILE "+_this.file+" COMPLETED");

                    }
                }
            });


        },

        proccessResponse:function(response){
            console.log(response);

            if (response.error==1){
                alert(response.message);
            }
            this.proccessLog(response);
        },

        _startImport:function(){

            var overlay = new Element("div");
            var dimensions = $("import-settings").getDimensions();

            overlay.setStyle({width:dimensions.width+"px",height:dimensions.height+"px",marginTop:-dimensions.height+"px"});
            overlay.addClassName("mr-overlay");
            this.overlay = overlay;
            $("import-settings").insert(this.overlay);
            var _this = this;
            new Ajax.Request(this.options.actionUrl,{
                type:"post",
                parameters:{action:"run",bucket:this.bucket,file:this.file,reindex:$("need_reindex").checked},
                onSuccess:function(data){
                    _this.proccessResponse(data.responseJSON);
                }
            });

            setTimeout(function(){
                _this.checkImport();
            },3000);
        },

        downloadReport:function(){
            new Ajax.Request(this.options.actionUrl,{
                type:"post",
                parameters:{action:"report"},
                onSuccess:function(data){
                    document.location = data.responseJSON.file;
                }
            });
        }

    });
    return MRImporter;
});