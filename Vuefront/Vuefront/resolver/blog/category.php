<?php

class ResolverBlogCategory extends Resolver
{
    public function get($data)
    {
        $this->load->model('blog/category');
        $category = $this->model_blog_category->getCategory($data['id']);

        $thumb      = '';
        $thumbLazy = '';

        return array(
            'id'             => $category['category_id'],
            'name'           => $category['title'],
            'description'    => $category['content'],
            'parent_id'      => (string) $category['parent_id'],
            'image'          => $thumb,
            'keyword'        => $category['identifier'],
            'imageLazy'      => $thumbLazy,
            'url'            => function ($root, $args) {
                return $this->url(array(
                    'parent' => $root,
                    'args'   => $args
                ));
            },
            'categories'     => function ($root, $args) {
                return $this->child(array(
                    'parent' => $root,
                    'args'   => $args
                ));
            }
        );
    }

    public function getList($args)
    {
        $this->load->model('blog/category');
        $filter_data = array(
            'limit'  => $args['size'],
            'start'  => ($args['page'] - 1) * $args['size'],
            'sort' => $args['sort'],
            'order'   => $args['order']
        );

        if ($args['parent'] !== -1) {
            $filter_data['filter_parent_id'] = $args['parent'];
        }

        $product_categories = $this->model_blog_category->getCategories($filter_data);

        $category_total = $this->model_blog_category->getTotalCategories($filter_data);

        $categories = array();

        foreach ($product_categories as $category) {
            $categories[] = $this->get(array( 'id' => $category['ID'] ));
        }

        return array(
            'content'          => $categories,
            'first'            => $args['page'] === 1,
            'last'             => $args['page'] === ceil($category_total / $args['size']),
            'number'           => (int) $args['page'],
            'numberOfElements' => count($categories),
            'size'             => (int) $args['size'],
            'totalPages'       => (int) ceil($category_total / $args['size']),
            'totalElements'    => (int) $category_total,
        );
    }

    public function child($data)
    {
        $this->load->model('blog/category');
        $category = $data['parent'];
        $filter_data = array(
            'filter_parent_id' => $category['id']
        );

        $blog_categories = $this->model_blog_category->getCategories($filter_data);

        $categories = array();

        foreach ($blog_categories as $category) {
            $categories[] = $this->get(array( 'id' => $category['ID'] ));
        }

        return $categories;
    }

    public function url($data)
    {
        $category_info = $data['parent'];
        $result = $data['args']['url'];

        $result = str_replace("_id", $category_info['id'], $result);
        $result = str_replace("_name", $category_info['name'], $result);

        if ($category_info['keyword'] != '') {
            $result = '/'.$category_info['keyword'];
        }

        return $result;
    }
}