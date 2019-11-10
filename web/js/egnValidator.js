function validateEgn( input ){

    if (input == null){
        input = '';
    }
    if(input.length !== 10){
      return false;
    }  else {
        if ( isNaN(input) ){
            return false;
        }else{
            let pos1 = Number(input.charAt(0))*2 ;
            let pos2 = Number(input.charAt(1))*4;
            let pos3 = Number(input.charAt(2))*8;
            let pos4 = Number(input.charAt(3))*5;
            let pos5 = Number(input.charAt(4))*10;
            let pos6 = Number(input.charAt(5))*9;
            let pos7 = Number(input.charAt(6))*7;
            let pos8 = Number(input.charAt(7))*3;
            let pos9 = Number(input.charAt(8))*6;
            let pos10 = Number(input.charAt(9));
            let controlPos10 = (pos1+pos2+pos3+pos4+pos5+pos6+pos7+pos8+pos9)%11;

            if (controlPos10 === pos10){
              return true;
            }else{
                if ( (controlPos10 === 10) && (pos10 === 0)){
                    return true
                }else{
                    return false;
                }

            }

        }
    }
}

function validate9CharBulstat(input) {
    //
    let pos1 = Number(input.charAt(0));
    let pos2 = Number(input.charAt(1))*2;
    let pos3 = Number(input.charAt(2))*3;
    let pos4 = Number(input.charAt(3))*4;
    let pos5 = Number(input.charAt(4))*5;
    let pos6 = Number(input.charAt(5))*6;
    let pos7 = Number(input.charAt(6))*7;
    let pos8 = Number(input.charAt(7))*8;
    let pos9 = Number(input.charAt(8));
    let controlPos9 = (pos1+pos2+pos3+pos4+pos5+pos6+pos7+pos8)%11;

    if (controlPos9 === pos9){
        return true;
    }else{
        if (controlPos9 === 10){
            let pos1 = Number(input.charAt(0))*3;
            let pos2 = Number(input.charAt(1))*4;
            let pos3 = Number(input.charAt(2))*5;
            let pos4 = Number(input.charAt(3))*6;
            let pos5 = Number(input.charAt(4))*7;
            let pos6 = Number(input.charAt(5))*8;
            let pos7 = Number(input.charAt(6))*9;
            let pos8 = Number(input.charAt(7))*10;
            let pos9 = Number(input.charAt(8));
            let controlPos9 = (pos1+pos2+pos3+pos4+pos5+pos6+pos7+pos8)%11;

            if (controlPos9 === pos9) {
                return true;
            }else{
                if ( (controlPos9 === 10) && (pos9 === 0)){
                    return true
                }else{
                    return false;
                }
            }
        }else{
            return false;
        }
    }
}

function validateBulstat(input){
    if (input == null){
        input = '';
    }
    if(input.length === 9 || input.length === 13){
        if ( isNaN(input) ){
            return false;
        }else{
            if (input.length === 9) {
                if (validate9CharBulstat(input)){
                    return true
                }else {
                    return false
                }
            }//(input.length == 9)
            else{//(input.length == 13)
                //ToDo : Да изнеса 13-цифрения БУЛСТАТ на отделна функция
                if (validate9CharBulstat(input)){
                    let pos9 = Number(input.charAt(8))*2;
                    let pos10 = Number(input.charAt(9))*7;
                    let pos11 = Number(input.charAt(10))*3;
                    let pos12 = Number(input.charAt(11))*5;
                    let pos13 = Number(input.charAt(12));
                    let controlPos13 = (pos9+pos10+pos11+pos12)%11
                    if (controlPos13 == pos13) {
                        return true;
                    }else{
                        if (controlPos9 == 10){
                            let pos9 = Number(input.charAt(8))*4;
                            let pos10 = Number(input.charAt(9))*9;
                            let pos11 = Number(input.charAt(10))*5;
                            let pos12 = Number(input.charAt(11))*7;
                            let controlPos13 = (pos9+pos10+pos11+pos12)%11

                            if (controlPos9 == pos9) {
                                return true;
                            }else{
                                if ( (controlPos9 == 10) && (pos9 == 0)){
                                    return true
                                }else{
                                    return false;
                                }
                            }

                        }else{
                            return false;
                        }
                    }
                }else {
                    return false
                }
            }//(input.length == 13)
        }
    }  else {
        return false;
    }
}

function renderEGNValidator(){
    console.log('validator is loaded');

    let formInput =  document.getElementById('rozz_bundle_holder_type_eGN');

    if (!formInput) {
        formInput = document.getElementById('form_egn');
    }

    let input = formInput.value;
    let egnIndicator = document.getElementById('egn-check');

    if (validateEgn(input) || validateBulstat(input)){
        egnIndicator.innerHTML = 'Валидно.';
        egnIndicator.style.color = 'green';
    }else{
        egnIndicator.innerHTML = 'Невалидно ЕГН или ЕИК!';
        egnIndicator.style.color = 'red';
    }

    formInput.addEventListener("change", function() {

        let input = formInput.value;

        //js валидация
        if (validateEgn(input) || validateBulstat(input)){
            egnIndicator.innerHTML = 'Валидно.';
            egnIndicator.style.color = 'green';
        }else{
            egnIndicator.innerHTML = 'Невалидно ЕГН или ЕИК!';
            egnIndicator.style.color = 'red';
        }

    });

}