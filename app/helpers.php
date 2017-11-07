<?php
/**
 * Created by PhpStorm.
 * User: SeungUk
 * Date: 2017. 11. 1.
 * Time: 오후 1:41
 */


/**
 * 마크다운 문자열을 HTML로 컴파일한다.
 *
 * @param string $text
 * @return string
 */
function markdown($text) {
    return (new Parsedown)->text($text);
}

/**
 * 컬렉션에 주어진 키의 포함 여부를 확인한다.
 *
 * @param \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection $collection
 * @param int $search
 * @return boolean
 */
function collection_contains($collection, $search)
{
    // DB 저장값 및 사용자 입력값에 따라 선택 값 자동 선택
    // 추가 했으면 psr-4 의 의해 자동 로딩이 안되므로 composer dump-autolad 를 해줘야한다
    return $collection->contains($search);
}