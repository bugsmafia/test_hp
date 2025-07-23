<?php

return array(
    0 => array(
        'id' => 26597,
        'name' => 'Номер линии MANGO OFFICE',
        'type' => 'text',
        'account_id' => 21788605,
        'code' => '',
        'sort' => 521,
        'is_api_only' => true,
        'enums' => null,
        'group_id' => 'leads_68881538394279',
        'required_statuses' => array(
        ),
        'entity_type' => 'leads',
        'remind' => null,
        '_links' => array(
            'self' => array(
                'href' => 'https://hyperpc.amocrm.ru/api/v4/leads/custom_fields/26597',
            ),
        ),
    ),
    1 => array(
        'id' => 28574,
        'name' => 'Источник трафика',
        'type' => 'tracking_data',
        'account_id' => 21788605,
        'code' => 'UTM_SOURCE',
        'sort' => 523,
        'is_api_only' => true,
        'enums' => null,
        'group_id' => 'leads_68881538394279',
        'required_statuses' => array(
        ),
        'entity_type' => 'leads',
        'remind' => null,
        '_links' => array(
            'self' => array(
                'href' => 'https://hyperpc.amocrm.ru/api/v4/leads/custom_fields/28574',
            ),
        ),
    ),
    2 => array(
        'id' => 28576,
        'name' => 'Тип трафика',
        'type' => 'tracking_data',
        'account_id' => 21788605,
        'code' => 'UTM_MEDIUM',
        'sort' => 524,
        'is_api_only' => true,
        'enums' => null,
        'group_id' => 'leads_68881538394279',
        'required_statuses' => array(
        ),
        'entity_type' => 'leads',
        'remind' => null,
        '_links' => array(
            'self' => array(
                'href' => 'https://hyperpc.amocrm.ru/api/v4/leads/custom_fields/28576',
            ),
        ),
    ),
    3 => array(
        'id' => 28578,
        'name' => 'Название рекламной кампании',
        'type' => 'tracking_data',
        'account_id' => 21788605,
        'code' => 'UTM_CAMPAIGN',
        'sort' => 525,
        'is_api_only' => true,
        'enums' => null,
        'group_id' => 'leads_68881538394279',
        'required_statuses' => array(
        ),
        'entity_type' => 'leads',
        'remind' => null,
        '_links' => array(
            'self' => array(
                'href' => 'https://hyperpc.amocrm.ru/api/v4/leads/custom_fields/28578',
            ),
        ),
    ),
    4 => array(
        'id' => 28580,
        'name' => 'Ключевое слово кампании',
        'type' => 'tracking_data',
        'account_id' => 21788605,
        'code' => 'UTM_TERM',
        'sort' => 526,
        'is_api_only' => true,
        'enums' => null,
        'group_id' => 'leads_68881538394279',
        'required_statuses' => array(
        ),
        'entity_type' => 'leads',
        'remind' => null,
        '_links' => array(
            'self' => array(
                'href' => 'https://hyperpc.amocrm.ru/api/v4/leads/custom_fields/28580',
            ),
        ),
    ),
    5 => array(
        'id' => 28582,
        'name' => 'GA UTM',
        'type' => 'text',
        'account_id' => 21788605,
        'code' => 'GA_UTM',
        'sort' => 527,
        'is_api_only' => true,
        'enums' => null,
        'group_id' => 'leads_68881538394279',
        'required_statuses' => array(
        ),
        'entity_type' => 'leads',
        'remind' => null,
        '_links' => array(
            'self' => array(
                'href' => 'https://hyperpc.amocrm.ru/api/v4/leads/custom_fields/28582',
            ),
        ),
    ),
    6 => array(
        'id' => 72967,
        'name' => 'Срочность покупки',
        'type' => 'radiobutton',
        'account_id' => 21788605,
        'code' => '',
        'sort' => 509,
        'is_api_only' => false,
        'enums' => array(
            0 => array(
                'id' => 151295,
                'value' => 'Горячий',
                'sort' => 500,
            ),
            1 => array(
                'id' => 151297,
                'value' => 'Теплый',
                'sort' => 1,
            ),
            2 => array(
                'id' => 151299,
                'value' => 'Холодный',
                'sort' => 2,
            ),
        ),
        'group_id' => null,
        'required_statuses' => array(
            0 => array(
                'pipeline_id' => 1825453,
                'status_id' => 32036010,
            ),
            1 => array(
                'pipeline_id' => 2161680,
                'status_id' => 32040594,
            ),
        ),
        'entity_type' => 'leads',
        'remind' => null,
        '_links' => array(
            'self' => array(
                'href' => 'https://hyperpc.amocrm.ru/api/v4/leads/custom_fields/72967',
            ),
        ),
    ),
    7 => array(
        'id' => 160493,
        'name' => '№ конфигурации',
        'type' => 'numeric',
        'account_id' => 21788605,
        'code' => '',
        'sort' => 533,
        'is_api_only' => false,
        'enums' => null,
        'group_id' => 'leads_70641541080890',
        'required_statuses' => array(
        ),
        'entity_type' => 'leads',
        'remind' => null,
        '_links' => array(
            'self' => array(
                'href' => 'https://hyperpc.amocrm.ru/api/v4/leads/custom_fields/160493',
            ),
        ),
    ),
    8 => array(
        'id' => 208855,
        'name' => 'Способ оплаты',
        'type' => 'select',
        'account_id' => 21788605,
        'code' => '',
        'sort' => 532,
        'is_api_only' => false,
        'enums' => array(
            0 => array(
                'id' => 411919,
                'value' => 'Наличными',
                'sort' => 500,
            ),
            1 => array(
                'id' => 411921,
                'value' => 'Банковской картой',
                'sort' => 1,
            ),
            2 => array(
                'id' => 411927,
                'value' => 'Безналичный расчет',
                'sort' => 2,
            ),
        ),
        'group_id' => 'leads_70641541080890',
        'required_statuses' => array(
        ),
        'entity_type' => 'leads',
        'remind' => null,
        '_links' => array(
            'self' => array(
                'href' => 'https://hyperpc.amocrm.ru/api/v4/leads/custom_fields/208855',
            ),
        ),
    ),
    9 => array(
        'id' => 285117,
        'name' => '№ Заказа',
        'type' => 'numeric',
        'account_id' => 21788605,
        'code' => '',
        'sort' => 529,
        'is_api_only' => false,
        'enums' => null,
        'group_id' => 'leads_70641541080890',
        'required_statuses' => array(
        ),
        'entity_type' => 'leads',
        'remind' => null,
        '_links' => array(
            'self' => array(
                'href' => 'https://hyperpc.amocrm.ru/api/v4/leads/custom_fields/285117',
            ),
        ),
    ),
    10 => array(
        'id' => 297989,
        'name' => 'roistat',
        'type' => 'text',
        'account_id' => 21788605,
        'code' => '',
        'sort' => 522,
        'is_api_only' => false,
        'enums' => null,
        'group_id' => 'leads_68881538394279',
        'required_statuses' => array(
        ),
        'entity_type' => 'leads',
        'remind' => null,
        '_links' => array(
            'self' => array(
                'href' => 'https://hyperpc.amocrm.ru/api/v4/leads/custom_fields/297989',
            ),
        ),
    ),
    11 => array(
        'id' => 315433,
        'name' => 'Статус покупателя',
        'type' => 'radiobutton',
        'account_id' => 21788605,
        'code' => '',
        'sort' => 530,
        'is_api_only' => false,
        'enums' => array(
            0 => array(
                'id' => 585635,
                'value' => 'Физическое лицо',
                'sort' => 500,
            ),
            1 => array(
                'id' => 585637,
                'value' => 'Юридическое лицо',
                'sort' => 1,
            ),
        ),
        'group_id' => 'leads_70641541080890',
        'required_statuses' => array(
        ),
        'entity_type' => 'leads',
        'remind' => null,
        '_links' => array(
            'self' => array(
                'href' => 'https://hyperpc.amocrm.ru/api/v4/leads/custom_fields/315433',
            ),
        ),
    ),
    12 => array(
        'id' => 315480,
        'name' => 'Адрес доставки',
        'type' => 'text',
        'account_id' => 21788605,
        'code' => '',
        'sort' => 531,
        'is_api_only' => false,
        'enums' => null,
        'group_id' => 'leads_70641541080890',
        'required_statuses' => array(
        ),
        'entity_type' => 'leads',
        'remind' => null,
        '_links' => array(
            'self' => array(
                'href' => 'https://hyperpc.amocrm.ru/api/v4/leads/custom_fields/315480',
            ),
        ),
    ),
    13 => array(
        'id' => 315483,
        'name' => 'Комментарий от клиента',
        'type' => 'textarea',
        'account_id' => 21788605,
        'code' => '',
        'sort' => 557,
        'is_api_only' => true,
        'enums' => null,
        'group_id' => 'leads_70641541080890',
        'required_statuses' => array(
        ),
        'entity_type' => 'leads',
        'remind' => null,
        '_links' => array(
            'self' => array(
                'href' => 'https://hyperpc.amocrm.ru/api/v4/leads/custom_fields/315483',
            ),
        ),
    ),
    14 => array(
        'id' => 315485,
        'name' => 'Промо-код',
        'type' => 'text',
        'account_id' => 21788605,
        'code' => '',
        'sort' => 556,
        'is_api_only' => true,
        'enums' => null,
        'group_id' => 'leads_70641541080890',
        'required_statuses' => array(
        ),
        'entity_type' => 'leads',
        'remind' => null,
        '_links' => array(
            'self' => array(
                'href' => 'https://hyperpc.amocrm.ru/api/v4/leads/custom_fields/315485',
            ),
        ),
    ),
    15 => array(
        'id' => 331789,
        'name' => 'Комментарий от менеджера',
        'type' => 'textarea',
        'account_id' => 21788605,
        'code' => '',
        'sort' => 558,
        'is_api_only' => false,
        'enums' => null,
        'group_id' => 'leads_70641541080890',
        'required_statuses' => array(
        ),
        'entity_type' => 'leads',
        'remind' => null,
        '_links' => array(
            'self' => array(
                'href' => 'https://hyperpc.amocrm.ru/api/v4/leads/custom_fields/331789',
            ),
        ),
    ),
    16 => array(
        'id' => 395987,
        'name' => 'Форма захвата',
        'type' => 'text',
        'account_id' => 21788605,
        'code' => '',
        'sort' => 501,
        'is_api_only' => false,
        'enums' => null,
        'group_id' => null,
        'required_statuses' => array(
        ),
        'entity_type' => 'leads',
        'remind' => null,
        '_links' => array(
            'self' => array(
                'href' => 'https://hyperpc.amocrm.ru/api/v4/leads/custom_fields/395987',
            ),
        ),
    ),
    17 => array(
        'id' => 409643,
        'name' => 'Продукт',
        'type' => 'select',
        'account_id' => 21788605,
        'code' => '',
        'sort' => 506,
        'is_api_only' => false,
        'enums' => array(
            0 => array(
                'id' => 727475,
                'value' => 'Ноутбук',
                'sort' => 1,
            ),
            1 => array(
                'id' => 727477,
                'value' => 'Аксессуар',
                'sort' => 2,
            ),
            2 => array(
                'id' => 1134186,
                'value' => 'Компьютер HYPERPC',
                'sort' => 3,
            ),
            3 => array(
                'id' => 1134188,
                'value' => 'Компьютер EPIX',
                'sort' => 4,
            ),
            4 => array(
                'id' => 1137500,
                'value' => 'Компьютер',
                'sort' => 500,
            ),
        ),
        'group_id' => null,
        'required_statuses' => array(
            0 => array(
                'pipeline_id' => 1825453,
                'status_id' => 32036010,
            ),
            1 => array(
                'pipeline_id' => 2161680,
                'status_id' => 32040594,
            ),
        ),
        'entity_type' => 'leads',
        'remind' => null,
        '_links' => array(
            'self' => array(
                'href' => 'https://hyperpc.amocrm.ru/api/v4/leads/custom_fields/409643',
            ),
        ),
    ),
    18 => array(
        'id' => 410291,
        'name' => 'Какая боль',
        'type' => 'text',
        'account_id' => 21788605,
        'code' => '',
        'sort' => 507,
        'is_api_only' => false,
        'enums' => null,
        'group_id' => null,
        'required_statuses' => array(
        ),
        'entity_type' => 'leads',
        'remind' => null,
        '_links' => array(
            'self' => array(
                'href' => 'https://hyperpc.amocrm.ru/api/v4/leads/custom_fields/410291',
            ),
        ),
    ),
    19 => array(
        'id' => 624857,
        'name' => 'Есть 100% оплата',
        'type' => 'checkbox',
        'account_id' => 21788605,
        'code' => '',
        'sort' => 561,
        'is_api_only' => false,
        'enums' => null,
        'group_id' => 'leads_40251547546785',
        'required_statuses' => array(
            0 => array(
                'pipeline_id' => 1849411,
                'status_id' => 142,
            ),
            1 => array(
                'pipeline_id' => 1849447,
                'status_id' => 142,
            ),
            2 => array(
                'pipeline_id' => 2135394,
                'status_id' => 142,
            ),
        ),
        'entity_type' => 'leads',
        'remind' => null,
        '_links' => array(
            'self' => array(
                'href' => 'https://hyperpc.amocrm.ru/api/v4/leads/custom_fields/624857',
            ),
        ),
    ),
    20 => array(
        'id' => 624859,
        'name' => 'Есть расходная + счет фактура',
        'type' => 'checkbox',
        'account_id' => 21788605,
        'code' => '',
        'sort' => 562,
        'is_api_only' => false,
        'enums' => null,
        'group_id' => 'leads_40251547546785',
        'required_statuses' => array(
            0 => array(
                'pipeline_id' => 1849411,
                'status_id' => 142,
            ),
            1 => array(
                'pipeline_id' => 1849447,
                'status_id' => 142,
            ),
            2 => array(
                'pipeline_id' => 2135394,
                'status_id' => 142,
            ),
        ),
        'entity_type' => 'leads',
        'remind' => null,
        '_links' => array(
            'self' => array(
                'href' => 'https://hyperpc.amocrm.ru/api/v4/leads/custom_fields/624859',
            ),
        ),
    ),
    21 => array(
        'id' => 661101,
        'name' => 'Сервис',
        'type' => 'text',
        'account_id' => 21788605,
        'code' => '',
        'sort' => 537,
        'is_api_only' => false,
        'enums' => null,
        'group_id' => 'leads_70641541080890',
        'required_statuses' => array(
        ),
        'entity_type' => 'leads',
        'remind' => null,
        '_links' => array(
            'self' => array(
                'href' => 'https://hyperpc.amocrm.ru/api/v4/leads/custom_fields/661101',
            ),
        ),
    ),
    22 => array(
        'id' => 661103,
        'name' => 'Цена за доставку',
        'type' => 'text',
        'account_id' => 21788605,
        'code' => '',
        'sort' => 538,
        'is_api_only' => true,
        'enums' => null,
        'group_id' => 'leads_70641541080890',
        'required_statuses' => array(
        ),
        'entity_type' => 'leads',
        'remind' => null,
        '_links' => array(
            'self' => array(
                'href' => 'https://hyperpc.amocrm.ru/api/v4/leads/custom_fields/661103',
            ),
        ),
    ),
    23 => array(
        'id' => 661105,
        'name' => 'Дата отправки Min',
        'type' => 'text',
        'account_id' => 21788605,
        'code' => '',
        'sort' => 540,
        'is_api_only' => true,
        'enums' => null,
        'group_id' => 'leads_70641541080890',
        'required_statuses' => array(
        ),
        'entity_type' => 'leads',
        'remind' => null,
        '_links' => array(
            'self' => array(
                'href' => 'https://hyperpc.amocrm.ru/api/v4/leads/custom_fields/661105',
            ),
        ),
    ),
    24 => array(
        'id' => 661107,
        'name' => 'Дата отправки Max',
        'type' => 'text',
        'account_id' => 21788605,
        'code' => '',
        'sort' => 541,
        'is_api_only' => true,
        'enums' => null,
        'group_id' => 'leads_70641541080890',
        'required_statuses' => array(
        ),
        'entity_type' => 'leads',
        'remind' => null,
        '_links' => array(
            'self' => array(
                'href' => 'https://hyperpc.amocrm.ru/api/v4/leads/custom_fields/661107',
            ),
        ),
    ),
    25 => array(
        'id' => 661109,
        'name' => 'Min дней на доставку',
        'type' => 'text',
        'account_id' => 21788605,
        'code' => '',
        'sort' => 542,
        'is_api_only' => true,
        'enums' => null,
        'group_id' => 'leads_70641541080890',
        'required_statuses' => array(
        ),
        'entity_type' => 'leads',
        'remind' => null,
        '_links' => array(
            'self' => array(
                'href' => 'https://hyperpc.amocrm.ru/api/v4/leads/custom_fields/661109',
            ),
        ),
    ),
    26 => array(
        'id' => 661111,
        'name' => 'Max дней на доставку',
        'type' => 'text',
        'account_id' => 21788605,
        'code' => '',
        'sort' => 543,
        'is_api_only' => true,
        'enums' => null,
        'group_id' => 'leads_70641541080890',
        'required_statuses' => array(
        ),
        'entity_type' => 'leads',
        'remind' => null,
        '_links' => array(
            'self' => array(
                'href' => 'https://hyperpc.amocrm.ru/api/v4/leads/custom_fields/661111',
            ),
        ),
    ),
    27 => array(
        'id' => 661113,
        'name' => 'Исходный адрес',
        'type' => 'text',
        'account_id' => 21788605,
        'code' => '',
        'sort' => 544,
        'is_api_only' => true,
        'enums' => null,
        'group_id' => 'leads_70641541080890',
        'required_statuses' => array(
        ),
        'entity_type' => 'leads',
        'remind' => null,
        '_links' => array(
            'self' => array(
                'href' => 'https://hyperpc.amocrm.ru/api/v4/leads/custom_fields/661113',
            ),
        ),
    ),
    28 => array(
        'id' => 661115,
        'name' => 'Полный адрес',
        'type' => 'text',
        'account_id' => 21788605,
        'code' => '',
        'sort' => 545,
        'is_api_only' => false,
        'enums' => null,
        'group_id' => 'leads_70641541080890',
        'required_statuses' => array(
        ),
        'entity_type' => 'leads',
        'remind' => null,
        '_links' => array(
            'self' => array(
                'href' => 'https://hyperpc.amocrm.ru/api/v4/leads/custom_fields/661115',
            ),
        ),
    ),
    29 => array(
        'id' => 661117,
        'name' => 'ФИАС',
        'type' => 'text',
        'account_id' => 21788605,
        'code' => '',
        'sort' => 546,
        'is_api_only' => true,
        'enums' => null,
        'group_id' => 'leads_70641541080890',
        'required_statuses' => array(
        ),
        'entity_type' => 'leads',
        'remind' => null,
        '_links' => array(
            'self' => array(
                'href' => 'https://hyperpc.amocrm.ru/api/v4/leads/custom_fields/661117',
            ),
        ),
    ),
    30 => array(
        'id' => 661119,
        'name' => 'Населенный пункт',
        'type' => 'text',
        'account_id' => 21788605,
        'code' => '',
        'sort' => 547,
        'is_api_only' => false,
        'enums' => null,
        'group_id' => 'leads_70641541080890',
        'required_statuses' => array(
        ),
        'entity_type' => 'leads',
        'remind' => null,
        '_links' => array(
            'self' => array(
                'href' => 'https://hyperpc.amocrm.ru/api/v4/leads/custom_fields/661119',
            ),
        ),
    ),
    31 => array(
        'id' => 661121,
        'name' => 'Почтовый индекс',
        'type' => 'text',
        'account_id' => 21788605,
        'code' => '',
        'sort' => 548,
        'is_api_only' => false,
        'enums' => null,
        'group_id' => 'leads_70641541080890',
        'required_statuses' => array(
        ),
        'entity_type' => 'leads',
        'remind' => null,
        '_links' => array(
            'self' => array(
                'href' => 'https://hyperpc.amocrm.ru/api/v4/leads/custom_fields/661121',
            ),
        ),
    ),
    32 => array(
        'id' => 661123,
        'name' => 'Квартира',
        'type' => 'text',
        'account_id' => 21788605,
        'code' => '',
        'sort' => 551,
        'is_api_only' => false,
        'enums' => null,
        'group_id' => 'leads_70641541080890',
        'required_statuses' => array(
        ),
        'entity_type' => 'leads',
        'remind' => null,
        '_links' => array(
            'self' => array(
                'href' => 'https://hyperpc.amocrm.ru/api/v4/leads/custom_fields/661123',
            ),
        ),
    ),
    33 => array(
        'id' => 661125,
        'name' => 'Улица',
        'type' => 'text',
        'account_id' => 21788605,
        'code' => '',
        'sort' => 549,
        'is_api_only' => false,
        'enums' => null,
        'group_id' => 'leads_70641541080890',
        'required_statuses' => array(
        ),
        'entity_type' => 'leads',
        'remind' => null,
        '_links' => array(
            'self' => array(
                'href' => 'https://hyperpc.amocrm.ru/api/v4/leads/custom_fields/661125',
            ),
        ),
    ),
    34 => array(
        'id' => 661127,
        'name' => 'Дом',
        'type' => 'text',
        'account_id' => 21788605,
        'code' => '',
        'sort' => 550,
        'is_api_only' => false,
        'enums' => null,
        'group_id' => 'leads_70641541080890',
        'required_statuses' => array(
        ),
        'entity_type' => 'leads',
        'remind' => null,
        '_links' => array(
            'self' => array(
                'href' => 'https://hyperpc.amocrm.ru/api/v4/leads/custom_fields/661127',
            ),
        ),
    ),
    35 => array(
        'id' => 661129,
        'name' => 'Длина посылки (см)',
        'type' => 'text',
        'account_id' => 21788605,
        'code' => '',
        'sort' => 552,
        'is_api_only' => false,
        'enums' => null,
        'group_id' => 'leads_70641541080890',
        'required_statuses' => array(
        ),
        'entity_type' => 'leads',
        'remind' => null,
        '_links' => array(
            'self' => array(
                'href' => 'https://hyperpc.amocrm.ru/api/v4/leads/custom_fields/661129',
            ),
        ),
    ),
    36 => array(
        'id' => 661131,
        'name' => 'Ширина посылки (см)',
        'type' => 'text',
        'account_id' => 21788605,
        'code' => '',
        'sort' => 553,
        'is_api_only' => false,
        'enums' => null,
        'group_id' => 'leads_70641541080890',
        'required_statuses' => array(
        ),
        'entity_type' => 'leads',
        'remind' => null,
        '_links' => array(
            'self' => array(
                'href' => 'https://hyperpc.amocrm.ru/api/v4/leads/custom_fields/661131',
            ),
        ),
    ),
    37 => array(
        'id' => 661133,
        'name' => 'Высота посылки (см)',
        'type' => 'text',
        'account_id' => 21788605,
        'code' => '',
        'sort' => 554,
        'is_api_only' => false,
        'enums' => null,
        'group_id' => 'leads_70641541080890',
        'required_statuses' => array(
        ),
        'entity_type' => 'leads',
        'remind' => null,
        '_links' => array(
            'self' => array(
                'href' => 'https://hyperpc.amocrm.ru/api/v4/leads/custom_fields/661133',
            ),
        ),
    ),
    38 => array(
        'id' => 661135,
        'name' => 'Вес посылки (кг)',
        'type' => 'text',
        'account_id' => 21788605,
        'code' => '',
        'sort' => 555,
        'is_api_only' => false,
        'enums' => null,
        'group_id' => 'leads_70641541080890',
        'required_statuses' => array(
        ),
        'entity_type' => 'leads',
        'remind' => null,
        '_links' => array(
            'self' => array(
                'href' => 'https://hyperpc.amocrm.ru/api/v4/leads/custom_fields/661135',
            ),
        ),
    ),
    39 => array(
        'id' => 661517,
        'name' => 'Доставка',
        'type' => 'select',
        'account_id' => 21788605,
        'code' => '',
        'sort' => 534,
        'is_api_only' => false,
        'enums' => array(
            0 => array(
                'id' => 1103913,
                'value' => 'Самовывоз',
                'sort' => 500,
            ),
            1 => array(
                'id' => 1103915,
                'value' => 'Необходима доставка',
                'sort' => 1,
            ),
        ),
        'group_id' => 'leads_70641541080890',
        'required_statuses' => array(
        ),
        'entity_type' => 'leads',
        'remind' => null,
        '_links' => array(
            'self' => array(
                'href' => 'https://hyperpc.amocrm.ru/api/v4/leads/custom_fields/661517',
            ),
        ),
    ),
    40 => array(
        'id' => 662081,
        'name' => 'Адрес пункта выдачи',
        'type' => 'text',
        'account_id' => 21788605,
        'code' => '',
        'sort' => 539,
        'is_api_only' => true,
        'enums' => null,
        'group_id' => 'leads_70641541080890',
        'required_statuses' => array(
        ),
        'entity_type' => 'leads',
        'remind' => null,
        '_links' => array(
            'self' => array(
                'href' => 'https://hyperpc.amocrm.ru/api/v4/leads/custom_fields/662081',
            ),
        ),
    ),
    41 => array(
        'id' => 663847,
        'name' => 'Срочная сборка',
        'type' => 'checkbox',
        'account_id' => 21788605,
        'code' => '',
        'sort' => 504,
        'is_api_only' => false,
        'enums' => null,
        'group_id' => null,
        'required_statuses' => array(
        ),
        'entity_type' => 'leads',
        'remind' => null,
        '_links' => array(
            'self' => array(
                'href' => 'https://hyperpc.amocrm.ru/api/v4/leads/custom_fields/663847',
            ),
        ),
    ),
    42 => array(
        'id' => 670073,
        'name' => 'Трек номер',
        'type' => 'text',
        'account_id' => 21788605,
        'code' => '',
        'sort' => 536,
        'is_api_only' => false,
        'enums' => null,
        'group_id' => 'leads_70641541080890',
        'required_statuses' => array(
        ),
        'entity_type' => 'leads',
        'remind' => null,
        '_links' => array(
            'self' => array(
                'href' => 'https://hyperpc.amocrm.ru/api/v4/leads/custom_fields/670073',
            ),
        ),
    ),
    43 => array(
        'id' => 677840,
        'name' => 'Создан заказ на производство',
        'type' => 'checkbox',
        'account_id' => 21788605,
        'code' => '',
        'sort' => 560,
        'is_api_only' => false,
        'enums' => null,
        'group_id' => 'leads_40251547546785',
        'required_statuses' => array(
            0 => array(
                'pipeline_id' => 1849411,
                'status_id' => 28480096,
            ),
            1 => array(
                'pipeline_id' => 2135394,
                'status_id' => 30446097,
            ),
        ),
        'entity_type' => 'leads',
        'remind' => null,
        '_links' => array(
            'self' => array(
                'href' => 'https://hyperpc.amocrm.ru/api/v4/leads/custom_fields/677840',
            ),
        ),
    ),
    44 => array(
        'id' => 678758,
        'name' => 'Сумма сходится везде',
        'type' => 'checkbox',
        'account_id' => 21788605,
        'code' => '',
        'sort' => 563,
        'is_api_only' => false,
        'enums' => null,
        'group_id' => 'leads_40251547546785',
        'required_statuses' => array(
            0 => array(
                'pipeline_id' => 1849411,
                'status_id' => 142,
            ),
            1 => array(
                'pipeline_id' => 1849447,
                'status_id' => 142,
            ),
            2 => array(
                'pipeline_id' => 2135394,
                'status_id' => 142,
            ),
        ),
        'entity_type' => 'leads',
        'remind' => null,
        '_links' => array(
            'self' => array(
                'href' => 'https://hyperpc.amocrm.ru/api/v4/leads/custom_fields/678758',
            ),
        ),
    ),
    45 => array(
        'id' => 682794,
        'name' => 'Видеокарта',
        'type' => 'text',
        'account_id' => 21788605,
        'code' => '',
        'sort' => 565,
        'is_api_only' => true,
        'enums' => null,
        'group_id' => 'leads_91581580307817',
        'required_statuses' => array(
        ),
        'entity_type' => 'leads',
        'remind' => null,
        '_links' => array(
            'self' => array(
                'href' => 'https://hyperpc.amocrm.ru/api/v4/leads/custom_fields/682794',
            ),
        ),
    ),
    46 => array(
        'id' => 682796,
        'name' => 'Процессор',
        'type' => 'text',
        'account_id' => 21788605,
        'code' => '',
        'sort' => 566,
        'is_api_only' => true,
        'enums' => null,
        'group_id' => 'leads_91581580307817',
        'required_statuses' => array(
        ),
        'entity_type' => 'leads',
        'remind' => null,
        '_links' => array(
            'self' => array(
                'href' => 'https://hyperpc.amocrm.ru/api/v4/leads/custom_fields/682796',
            ),
        ),
    ),
    47 => array(
        'id' => 682798,
        'name' => 'Материнская плата',
        'type' => 'text',
        'account_id' => 21788605,
        'code' => '',
        'sort' => 567,
        'is_api_only' => true,
        'enums' => null,
        'group_id' => 'leads_91581580307817',
        'required_statuses' => array(
        ),
        'entity_type' => 'leads',
        'remind' => null,
        '_links' => array(
            'self' => array(
                'href' => 'https://hyperpc.amocrm.ru/api/v4/leads/custom_fields/682798',
            ),
        ),
    ),
    48 => array(
        'id' => 682800,
        'name' => 'Охлаждение',
        'type' => 'text',
        'account_id' => 21788605,
        'code' => '',
        'sort' => 568,
        'is_api_only' => true,
        'enums' => null,
        'group_id' => 'leads_91581580307817',
        'required_statuses' => array(
        ),
        'entity_type' => 'leads',
        'remind' => null,
        '_links' => array(
            'self' => array(
                'href' => 'https://hyperpc.amocrm.ru/api/v4/leads/custom_fields/682800',
            ),
        ),
    ),
    49 => array(
        'id' => 682802,
        'name' => 'Оперативная память',
        'type' => 'text',
        'account_id' => 21788605,
        'code' => '',
        'sort' => 569,
        'is_api_only' => true,
        'enums' => null,
        'group_id' => 'leads_91581580307817',
        'required_statuses' => array(
        ),
        'entity_type' => 'leads',
        'remind' => null,
        '_links' => array(
            'self' => array(
                'href' => 'https://hyperpc.amocrm.ru/api/v4/leads/custom_fields/682802',
            ),
        ),
    ),
);