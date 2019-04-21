/**
 * Created by Jivko on 25.4.2018 г..
 */
function newContract(){
    // вид договор бутон клик
    function contractTypeButtonClick() {
        $('#contract-types-btn').on('click',function () {
            console.log('contract types was clicked');
            console.log($(this).attr('id'));
            $('.btn-success').removeClass('btn-success');
            $(this).addClass('btn-success');
            render();
        });
    }

    // избрани имоти бутон клик
    function selectedLandsButtonClick() {
        $('#selected-lands-btn').on('click',function () {
            console.log('selected lands was clicked');
            $('.btn-success').removeClass('btn-success');
            $('#selected-lands-btn').addClass('btn-success');
            render();
        });
    }

    // наематели бутон клик
    function holdersButtonClick() {
        $('#holders-btn').on('click',function () {
            console.log('holders was clicked');
            $('.btn-success').removeClass('btn-success');
            $(this).addClass('btn-success');
            render();
        });
    }

    // срок и заповеди бутон клик
    function dataButtonClick() {
        $('#contract-data-btn').on('click',function () {
            console.log('contract data was clicked');
            $('.btn-success').removeClass('btn-success');
            $(this).addClass('btn-success');
            render();
        });
    }

    // преглед бутон клик
    function previewButtonClick() {
        $('#preview-btn').on('click',function () {
            console.log('preview was clicked');
            $('.btn-success').removeClass('btn-success');
            $(this).addClass('btn-success');
            render();
        });
    }




    // промяна на вида на договора
    function newContractTypeSelect(){

        $('#contract-types').change(function () {

            $('.loader-background').show('fast');
            let newType = $('#contract-types').val();
            let newTypeData = {'type' : newType};

            $.ajax({url: "/newcontract/update",
                type : 'post',
                dataType:   'json',
                async:      true,
                data: newTypeData,

                success: function(result){
                    console.log(result);
                    let ObjectResult = JSON.parse(result);
                    $.toaster({ priority : 'success', title : 'Вид Договор', message : ObjectResult.success});
                    $('.loader-background').fadeOut('fast');
                }
            });
        })
    }


    function render(){
        $('.loader-background').show('fast');
        let activeButton = $('.btn-success');
        $('.active').removeClass('active').hide();

        if ( activeButton.attr('id') === 'contract-types-btn' ){
            $('.contract-types').addClass('active');
            $.ajax({url: "/newcontract/update",
                type : 'get',
                dataType:   'json',
                async:      true,
                // data: newTypeData, //с GET  взимам данни за визуализацията

                success: function(result){

                    let ObjectResult = JSON.parse(result);
                    $('#contract-types').val(ObjectResult.type);
                    console.log(ObjectResult.type);
                    $('.loader-background').fadeOut('fast');
                }
            });
        }else if ( activeButton.attr('id') === 'selected-lands-btn' ){
            $('.selected-lands').addClass('active');
            $('.loader-background').fadeOut('fast');
        }else if ( activeButton.attr('id') === 'holders-btn' ){
            $('.holder').addClass('active');
            $('.loader-background').fadeOut('fast');
        }else if ( activeButton.attr('id') === 'contract-data-btn' ){
            $('.contract-data').addClass('active');
            $('.loader-background').fadeOut('fast');
        }else if ( activeButton.attr('id') === 'preview-btn' ){
            $('.contract-preview').addClass('active');
            $('.loader-background').fadeOut('fast');
        }
        $('.active').show();
    }

    //buttons click events
    render();
    contractTypeButtonClick();
    selectedLandsButtonClick();
    holdersButtonClick();
    dataButtonClick();
    previewButtonClick();

    newContractTypeSelect();
}
