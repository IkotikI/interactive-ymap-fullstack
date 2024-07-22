var myMap;

jQuery(document).ready(function ($) {


    function getNested(obj, ar_keys) {
        var innerObj = obj;
        for (var i = 0; i < ar_keys.length; i++) {
            innerObj = innerObj[ar_keys[i]];
            if (typeof innerObj === "undefined") return undefined;
            if (innerObj.toString().indexOf('[native code]') > -1) return undefined;
        }
        return innerObj;
    }
    const data_url = map_handler_data.data_url;
    const ajax_url = map_handler_data.ajax_url;
    let data;
    let elements = [];
    let hidden_parents = [];


    ymaps.ready(init);

    function addElements() {
        if (data.rendering !== undefined) {
            for (const key in data.rendering) {
                const element = data.rendering[key]
                elements[key] = $(element.selector)
            }
        }
    }

    function addPoints() {
        let branch_buttons = $('#branch_buttons');
        let btn;
        for (const key in data.points.features) {
            point = data.points.features[key]
            btn = $(`<button class="change_branch" data-id="${point.id}" />${point.id}</button>`)
            branch_buttons.append(btn)
            btn.click(event => {
                const id = $(event.target).attr('data-id')
                renderElement(id)
            })
        }
    }

    function renderElement(id) {
        // Display hidden parents
        if (hidden_parents.length > 0) {
            for ( el of hidden_parents ) {
                el.show()
            }
            hidden_parents = [];
        }
        // Hide clear undefined fields. Hide parents.
        for (const field_key in elements) {
            const field = getNested(data.info, [id, field_key])
            if (field === undefined || field !== undefined && Array.isArray(field) && field[0] == false) {
                elements[field_key].html('')
                const parent_level = getNested(data.rendering, [field_key, 'empty-hide-parent'])
                // console.log('parent_level[',field_key,']', parent_level)
                if (parent_level !== undefined && parent_level >= 1) {
                    const parent_to_hide = elements[field_key].parents().eq(parent_level-1);
                    // console.log('adding parent to hide',elements[field_key].parents(), parent_to_hide)
                    parent_to_hide.hide()
                    hidden_parents.push(parent_to_hide)
                }
                continue
            }
        // Render new data
        let output = ""
        for (const value of field) {
            if (data.rendering[field_key].template !== undefined) {
                output += data.rendering[field_key].template.replaceAll("{{@}}", value)
            } else {
                output += value
            }
        }
        elements[field_key].html(output)
            
        }
    }

    function scrollToElement() {
        document.querySelector('#branch_info').scrollIntoView({
            behavior: 'smooth'
        });
        // window.scrollBy(0, -40);
    }

    function init() {
        myMap = new ymaps.Map('map', {
            center: [55.76, 37.64],
            zoom: 10
        }, {
            searchControlProvider: 'yandex#search'
        });

        objectManager = new ymaps.ObjectManager({
            // Чтобы метки начали кластеризоваться, выставляем опцию.
            // clusterize: true,
            geoObjectOpenBalloonOnClick: false,
            clusterOpenBalloonOnClick: false
        });

        let geo_objets = myMap.geoObjects.add(objectManager);

        $.ajax({
            url: data_url,
        }).done(function (response) {
            data = response;
            console.log('data', data);
            objectManager.add(data.points);
            addElements();
            addPoints();

            renderDefault();
            selectCityHandler();
            // renderElement(data.points.features[0].id)
        });

        function onObjectEvent(e) {
            var objectId = e.get('objectId');
            const target = $('#branch_content')

            if (e.get('type') == 'click') {
                renderElement(objectId);
                scrollToElement();
            }

        }

        objectManager.objects.events.add(['click'], onObjectEvent);


        
        function renderDefault() {
            const onload = data.rendering._onload;
            if ( onload !== undefined ) {
                if ( onload === false ) return;
                if ( onload === true ) {
                    renderElement(data.points.features[0].id)
                }
                if ( Number.isInteger(onload) ) {
                    renderElement(onload)
                }
            }
        }

        function selectCityHandler(){
            const select_wrap = $('.select-wrap');
            const select_button = $('.select-button');
            const select = $('.cities-select');
            const radios = select.find('input');
            console.log(radios);
        
            select_button.each(function(){
                $(this).click(function() {
                    $(this).closest('.select-wrap').toggleClass('active');
                    // console.log('.select-button click');
                })
            });
        
            radios.each(function(){
                $(this).change(function(){
                    select_button.find('.name').html( $(this).siblings('label').text() );
                    setCity($(this).val());
                    // $(this).closest('.select-wrap').toggleClass('active')
                    radios.prop('checked', false).removeClass('active')
                    // const attr_id = ;
                    // console.log('attr_id', attr_id, select.find('#'+attr_id));
                    select.find('#'+$(this).attr('id')).addClass('active')
                    $(this).closest('.select-wrap').toggleClass('active');
                })
            });

            window.addEventListener('click', function(e){   
                if (!select_wrap.is(e.target) && select_wrap.has(e.target).length === 0 ){
                    select_wrap.removeClass('active');
                }
            });
        }

        function setCity(city_id) {
            if ( getNested(data, ['cities', city_id] ) === undefined ) {
                return;
            } 
            const city = data.cities[city_id];
            const coordinates = city.coordinates;

            if ( coordinates !== undefined ) {
                const zoom = city.zoom ?? 10;
                myMap.setCenter(coordinates, zoom)
            }

            const render_id = city.render_id
            if ( render_id !== undefined ) { 
                renderElement(render_id)
            } else {
                for ( id in data.info ) {
                    const _city_id = data.info[id]._city_id
                    if (_city_id !== undefined && _city_id == city_id ) {
                        renderElement(id)
                        break
                    }
                }
            }
        }

    }

    });




