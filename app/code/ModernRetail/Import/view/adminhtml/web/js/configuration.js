define([
    "jquery",
], function($){
	
	var MRImportMapping = { };
	MRImportMapping = Class.create({
	    root:null,
	    options:null,
	    table:null,
	    initialize: function(root,options) {
	        this.root = root;
	        this.table = root.getElementsBySelector("tbody")[0];
	        this.options = options;
	
	        var self = this;
	        self.root.getElementsBySelector('.add-new-mapping').each(function(element){
	
	            element.observe('click',function(){
	                self.newMapRow();
	            });
	
	        });
	
	        self.bindInputs();
	    },
	
	    collectData:function(){
	        var data = {};
	        var self = this;
	        var elements = self.table.getElementsBySelector("input.tagName");
	        for (var i =0;i<elements.length;i++){
	            if(!elements[i].value) continue;
	
	            data[elements[i].value] = {
	                attribute:self.table.getElementsBySelector(".attributeName")[i].value,
	                status:self.table.getElementsBySelector(".toggler")[i].checked,
	                is_configurable:self.table.getElementsBySelector(".is_configurable")[i].checked
	            };
	        }
	        self.options.valueField.value = Object.toJSON(data);
	        console.log(self.options.valueField);
	    },
	
	    bindInputs:function(){
	        var self = this;
	        self.table.getElementsBySelector(".toggler").each(function(element){
	            element.observe("change",function(){
	                if (this.checked==true){
	                    element.parentNode.parentNode.setStyle({opacity:1});
	                }else {
	                    element.parentNode.parentNode.setStyle({opacity:0.2});
	                }
	                self.collectData();
	            });
	        });
	
	        self.table.getElementsBySelector(".is_configurable").each(function(element){
	            element.observe('change',function(){
	                self.collectData();
	            });
	        });
	
	
	        self.table.getElementsBySelector(".remove").each(function(element){
	            element.observe('click',function(){
	                this.parentNode.parentNode.remove();
	                self.collectData();
	            });
	        });
	
	        self.table.getElementsBySelector("input").each(function(element){
	            element.observe('keyup',function(){
	                self.collectData();
	            });
	        });
	
	    },
	
	    newMapRow:function(){
	        //console.log(this.root);
	        var a = new Element("tr");
	
	        var a = this.table.getElementsBySelector(".example")[0].clone(true);
	        this.table.appendChild(a);
	
	        this.bindInputs();
	        console.log(this.root);
	        a.show();
	        return false;
	    }
	
	
	
	});
	return MRImportMapping;
	 
});
