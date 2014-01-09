VocabularyBundle
================

This bundle lets you manage and use lists of data like countries, cities,
subway station or categories in your project. Also it can be used for i18n.

All you need to do is simply to create file with vocabulary data. Lets say
you want create categories for items in online shop.

Installation
------------
Composer:

    "nord-ua/vocabulary-bundle": "2.0.*@dev"

How does it works
-----------------
Each value in dictionary consist of 3 field: language, search key (somehow
it called *slug*) and, actually, value. In items you must store slugs of
vocabulary values. Later ````VocabularyService```` will give you actual values
for them.


Populate vocabulary
-------------------
1. Create file @BundleName/Resources/vocabulary/**category**.**en**.txt
1. Fill the file with categories, just one category name per line
1. Run ````cleverbag:vocabulary:load```` task to populate database

Retrieving all the values
-------------------------
    $container->get('vocabulary.service')->getRoots('category');

Retrieving specific values
---------------------
When you need output human readable values of categories:

    $container->get('vocabulary.service')->vocabularyValues([/* slugs */], 'category');

In template
-----------
Use ````vocabularyValues```` or ````vocabularyValue```` filters:

    {% for category in item.categories|vocabularyValues('category') %}
      {{ category }}
    {% endfor %}

File syntax
===========
In simple cases file can just contain values for categories, one on a row. Slug will be
generated when vocabulary loading. Important feature is that slugs must be and will be
equal on all servers (devs and prods). But if you wish set slugs manually, you can put
them on beginning of string and separate with colon from value:

    cat1: Category 1
    cat2: category 2
    ...

Params
======
Every vocabulary value can have custom params. E.g. Moscow subway have many stations with
same name, but located on different branches [example](http://metro.yandex.ru/moscow/).
Params are useful to store important data:

    culture-red: Culture park [branch=red]
    culture-ring: Culture park [branch=ring]
    cmsm-red: Comsomolskaya [branch=red]
    cmsm-ring: Comsomolskaya [branch=ring]

Or, to not to repeat same branch param, you can group lines by same params:

    [branch=red]
    culture-red: Culture park
    cmsm-red: Comsomolskaya
    ----

    lbrr: Lenin Library

    [branch=ring]
    culture-ring: Culture park
    cmsm-ring: Comsomolskaya

Pay attention to line ````---```` - it clear common params, so **Lenin Library** station
will not have any *branch* param.


Trees
=====
Trees are special case for params: you can group you items by parent, just set ````parent```` param.
E.g. to create categories tree:

// category.en.txt

    cat1: Category 1
    cat2: Category 2

    [parent=cat1]
    subcat11: Category 1 Subcategory 1
    subcat12: Category 1 Subcategory 2
    subcat13: Category 1 Subcategory 3
    ---

    [parent=cat1]
    subcat11: Category 2 Subcategory 1
    subcat22: Category 2 Subcategory 2
    subcat33: Category 2 Subcategory 3
    ---

Once again, do not forget ````---```` line!
To retrieve subcategories, simply call

    $container->get('vocabulary.service')->preloadTree('category');
    $container->get('vocabulary.service')->vocabularyValue('cat1', 'category')->getChildren();

Precache
========
By default values are loaded in moment of need. In case of categories you know before
you need most of them. So you can precache values by calling ````preload```` or ````preloadTree````.
In twig template you can call ````{{ vocabulary.preload('country') }}````.

