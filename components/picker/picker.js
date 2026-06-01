(function(){

    function closeAll(){
        document
            .querySelectorAll('.picker.open')
            .forEach(p => p.classList.remove('open'));
    }

    function positionPicker(picker){

        const menu = picker.querySelector('.picker-menu');

        picker.classList.remove('up');

        menu.style.display = 'block';

        const rect = picker.getBoundingClientRect();
        const menuHeight = menu.offsetHeight;

        menu.style.display = '';

        const spaceBelow =
            window.innerHeight - rect.bottom;

        const spaceAbove =
            rect.top;

        if(
            spaceBelow < menuHeight + 10 &&
            spaceAbove > spaceBelow
        ){
            picker.classList.add('up');
        }
    }

    document.addEventListener('click', e => {

        const trigger =
            e.target.closest('.picker-trigger');

        if(trigger){

            const picker =
                trigger.closest('.picker');

            const isOpen =
                picker.classList.contains('open');

            closeAll();

            if(!isOpen){

                positionPicker(picker);

                picker.classList.add('open');
            }

            return;
        }

        const option =
            e.target.closest('.picker-option');

        if(option){

            const picker =
                option.closest('.picker');

            picker
                .querySelectorAll('.picker-option')
                .forEach(o =>
                    o.classList.remove(
                        'active',
                        'focused'
                    )
                );

            option.classList.add('active');

            picker.dataset.value =
                option.dataset.value;

            picker.querySelector(
                '.picker-value'
            ).textContent =
                option.textContent.trim();

            picker.classList.remove('open');

            picker.dispatchEvent(
                new CustomEvent(
                    'change',
                    {
                        detail:{
                            value:
                                option.dataset.value,
                            text:
                                option.textContent.trim()
                        }
                    }
                )
            );

            return;
        }

        closeAll();
    });

    document.addEventListener('keydown', e => {

        const picker =
            document.querySelector(
                '.picker.open'
            );

        if(!picker){
            return;
        }

        const options =
            [...picker.querySelectorAll(
                '.picker-option'
            )];

        let focused =
            picker.querySelector(
                '.picker-option.focused'
            );

        let index =
            options.indexOf(focused);

        if(e.key === 'Escape'){

            picker.classList.remove('open');
            return;
        }

        if(e.key === 'ArrowDown'){

            e.preventDefault();

            if(index < options.length - 1){
                index++;
            }else{
                index = 0;
            }

            options.forEach(o =>
                o.classList.remove('focused')
            );

            options[index]
                .classList.add('focused');

            return;
        }

        if(e.key === 'ArrowUp'){

            e.preventDefault();

            if(index > 0){
                index--;
            }else{
                index = options.length - 1;
            }

            options.forEach(o =>
                o.classList.remove('focused')
            );

            options[index]
                .classList.add('focused');

            return;
        }

        if(
            e.key === 'Enter' &&
            focused
        ){
            focused.click();
        }
    });

})();