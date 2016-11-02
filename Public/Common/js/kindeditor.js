$(document).ready(function(){
    KindEditor.ready(function(K) {
        window.editor = K.create('#text-content',{
            items:[
                'source', '|', 'undo', 'redo', '|', 'cut', 'copy','|', 'justifyleft', 'justifycenter', 'justifyright',
                'justifyfull', 'clearhtml', 'selectall', '|', 'formatblock', 'fontname', 'fontsize', '|', 'forecolor',
                'hilitecolor', 'bold', 'italic', 'underline', 'strikethrough','image'
            ],
            afterBlur:function(){this.sync();}
        });
        window.editor = K.create('#text-content', {
            allowFileManager : true
        });
    });
});
