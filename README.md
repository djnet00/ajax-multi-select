# ajax-multi-select

# Examples
[`demo`](https://alexandrmihailovich.github.io/ajax-multi-select/test.html)


```html
<select multiple="" id="select-1"></select>
```

```javascript
$('select').selectWidget({
        'maxSelect' 	: 8,
        'autoOpen'  	: false,
        'autoClear' 	: false,
        'hideSelected' 	: false,
        'minLength' 	: 3,
        'url'           : 'https://autocomplete.geocoder.api.here.com/6.2/suggest.json',
        'dataQuery'  : 'query',
        'requestData'   : {
            'app_id'    : 'KANwiewaDDXazappMZfV',
            'app_code'  : 'aUHzNr7xisoJCaSx0nb36w',
        },
        'respondVars'   : {
            value     : 'locationId',
            title     : 'label',
            container : 'suggestions'
        }
    });
```

```javascript  
{
  'maxSelect'     : 999,
'url'			: 'http://localhost/',
                'autoOpen'		: true,
                'autoClear'		: true,
                'hideSelected'	: false,
                'minLength'		: 3,
                'requestType'   : 'GET',
                'classPrefix'   : '',
                'dataType'      : 'json',
                'requestData'   : {
                    action: 	'action'
                },
                'dataQuery'     : 'q',
                'respondVars'   : {
                    value     : 'value',
                    title     : 'title',
                    container : false
                }
            }
```
