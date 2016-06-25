# SlimPagination
simple pagination for slim3

## Requirements

 PHP 7.0 or higher
 
 Slim 3.0
 
## Installation
via composer:
`composer require xandros15/slim-pagination`
## Basic Usage

Create a object of pagination inside of controller callback, fetch it into string via renderer and pass it as variable:

```php
<?php
 use Xandros15\SlimPagination\Pagination;
 $app->get('/users', function (Request $request, Response $response) {
 $users = User::search($request);
 $pagination = new Pagination($request, $this->get('router'),[
                Pagination::OPT_TOTAL => count($users), //number of items
            ]);
 $paginationString = $this-view->fetch('pagination', ['pagination' => $pagination]);
 return $this->view->render('user.index', ['pagination' => $paginationString]);
 })->name('user.index');

```

Them render if via template manager.

### PHP template example
```php
<nav class="text-center col-xs-12">
    <ul class="pagination">
        <?php /** @var $pagination Xandros15\SlimPagination\Pagination */ ?>
        <?php if ($pagination->previous()['isCurrent']): ?>
            <li class="disabled">
                <span><?= $pagination->previous()['pageName']?></span>
            </li>
        <?php else: ?>
            <li>
                <a aria-label="previous" href="<?= $pagination->previous()['pathFor']?>">
                    <span aria-hidden="true"><?= $pagination->previous()['pageName']?></span>
                </a>
            </li>
        <?php endif ?>
        <?php foreach ($pagination as $page): ?>
            <?php if ($page['isSlider']): ?>
                <li class="disabled">
                    <span><?= $page['pageName']?></span>
                </li>
            <?php elseif ($page['isCurrent']): ?>
                <li class="active">
                    <span><?= $page['pageName']?></span>
                </li>
            <?php else: ?>
                <li>
                    <a href="<?= $page['pathFor']?>"><?= $page['pageName']?></a>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>
        <?php if ($pagination->next()['isCurrent']): ?>
            <li class="disabled">
                <span><?= $pagination->next()['pageName']?></span>
            </li>
        <?php else: ?>
            <li>
                <a aria-label="next" href="<?= $pagination->next()['pathFor']?>">
                    <span aria-hidden="true"><?= $pagination->next()['pageName']?></span>
                </a>
            </li>
        <?php endif ?>
    </ul>
</nav>
```

### Twig template example
```twig
{% if pagination.canCreate %}
    <nav class="text-center col-xs-12">
        <ul class="pagination">
            {% if pagination.previous.isCurrent %}
                <li class="disabled">
                    <span>{{ pagination.previous.pageName | raw }}</span>
                </li>
            {% else %}
                <li>
                    <a aria-label="previous" href="{{ pagination.previous.pathFor }}">
                        <span aria-hidden="true">{{ pagination.previous.pageName | raw }}</span>
                    </a>
                </li>
            {% endif %}
            {% for page in pagination %}
                {% if page.isSlider %}
                    <li class="disabled"><span>{{ page.pageName }}</span></li>
                {% elseif page.isCurrent %}
                    <li class="active"><span>{{ page.pageName }}</span></li>
                {% else %}
                    <li><a href="{{ page.pathFor}}">{{ page.pageName }}</a></li>
                {% endif %}
            {% endfor %}
            {% if pagination.next.isCurrent %}
                <li class="disabled">
                    <span aria-hidden="true">{{ pagination.next.pageName | raw }}</span>
                </li>
            {% else %}
                <li>
                    <a aria-label="next" href="{{ pagination.next.pathFor }}">
                        <span aria-hidden="true">{{ pagination.next.pageName | raw }}</span>
                    </a>
                </li>
            {% endif %}
        </ul>
    </nav>
{% endif %}
```

## Options

| Name  | Info | Default | More |
|---|---|---|---|
| `Pagination::OPT_TOTAL` | set to total of items | (int) 1 |-|
| `Pagination::OPT_PARAM_NAME` | set param name | (string) page |example `?page=2` or route: `/[{page:\d+}]` has param name: `page`|
| `Pagination::OPT_PARAM_TYPE` | set param type (e.g via query or attribute) | (const) `PageList::PAGE_QUERY` | `PageList::PAGE_QUERY` for this build ?page=2 `PageList::PAGE_ATTRIBUTE` for this build /page/2 |
| `Pagination::OPT_PER_PAGE` | set how many items should be show on one page | (int) 10 |-|
| `Pagination::OPT_SIDE_LENGTH` | set how many buttons should be show before slider | (int) 3 |-|
| `Pagination::OPT_LIST_TYPE` | set type of list | (const) `PageList::NORMAL` | available `PageList::NORMAL`, `PageList::MINI`, `PageList::NONE` |

### PageList Type:

`PageList::NORMAL` is normal pagination with slider:

![normal](https://cloud.githubusercontent.com/assets/10834079/16358420/a936bb66-3b12-11e6-84b2-01f643e748c8.png)

 `PageList::MINI` is minimalistic pagination:
 
![mini](https://cloud.githubusercontent.com/assets/10834079/16358438/f3a806fa-3b12-11e6-9604-2caa16e80c86.png)
 
 `PageList::NONE` turns off pagination
 
## Methods and Attributes

### Page 

`pathFor` - returning path for this page

`isCurrent` - check if this page is current

`pageName` - returning page name (e.g. number)

`isSlider` - check if this page is slider

### Pagination

`previous()` - getting previous page, if doesn't exist returning current

`next()` - getting next page, if doesn't exist returning current

`first()` - getting first page

`last()` - getting last page

`canCreate()` - checking if pagination can be create

`toArray()` - returning array of defined params:
     - per_page: how many items on one page
     - current_page: number of current page
     - next_page_url: path for next page
     - prev_page_url: path for previous page
     - from: number of first item
     - to: number of last item

`toJson()` - same as toArray(), just compile to json string
