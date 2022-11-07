<?php

namespace mmaurice\cabinet\core\events;

use mmaurice\cabinet\core\prototypes\EventPrototype;

/**
 * Событие OnLoadWebDocumentEvent
 * 
 * Срабатывает при попытке обращения к существующей странице.
 * Для рендера личного кабинета в окружении шаблона сайта, необходимо перехватывать одну из страниц сайта. Как правило
 *  это специально созданная страница. Например, lk. Поэтому тут необходимо определить, является ли существующая
 *  страница хэндлером ЛК.
 */
class OnLoadWebDocumentEvent extends EventPrototype
{
    
}
