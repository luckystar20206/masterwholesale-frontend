var config = {
    paths: {
        'mr_import/configuration': 'ModernRetail_Import/js/configuration',
        'mr_import/importer':'ModernRetail_Import/js/importer'
    },
    shim: {
        'mr_import/configuration': {
            deps: ['prototype']
        },
        'mr_import/importer': {
            deps: ['prototype']
        }
    }
};
