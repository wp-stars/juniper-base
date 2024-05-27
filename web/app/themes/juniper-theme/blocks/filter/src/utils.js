import React from "react";
import FilterCheckbox from "./newComponents/SingleFilterComponents/FilterCheckbox";
import FilterDropdown from "./newComponents/SingleFilterComponents/FilterDropdown";
import axios from "axios";

export function isIterable(obj) {
    // checks for null and undefined
    return typeof obj[Symbol.iterator] === 'function';
}

export function rerenderSlick() {
    const event = new Event('filterRenderingDone');
    document.dispatchEvent(event);
}

export function clone(obj) {
    console.log(JSON.stringify(obj))

    return JSON.parse(JSON.stringify(obj))
}

export async function loadInPostsFromPage(restUrl = '', postType = 'product', pageNum = 0) {
    const endpoint = `${restUrl}wps/v1/data?post_type=${postType}&page=${pageNum}`
    const response = await axios.get(endpoint)

    const responseData = response.data ?? {posts: []}
    return responseData.posts;
}

export function renderPost(post, index) {
    return <div key={index}
                className={'flex flex-col h-full col-span-3 sm:col-span-1 gap-y-14 sm:gap-y-0 flex-grow'}
                dangerouslySetInnerHTML={{ __html: atob(post.html) }}
    />
}

export function getUrlParamValue(param) {
    const currentQuery = window.location.search.substring(1)
    
    const vars = currentQuery.split('&')

    const queryArray = vars.map((keyValue) => {
        const key = keyValue.split('=')[0]
        const value = keyValue.split('=')[1]
        return {key: key, value: value}
    })
    
    const foundValue = queryArray.find((element) => element.key === param)

    return foundValue ? foundValue.value : null
}


export function filterOptionToElement(filterOption) {
    switch (filterOption.type) {
        case 'checkbox':
            return <FilterCheckbox data={filterOption}/>
        case 'dropdown':
            return <FilterDropdown data={filterOption}/>
        default:
            return 'hello'
    }
}

export function postApplysToTax(post, tax, value) {
    const taxonomies = post.taxonomies

    const taxonomyToChecExists = taxonomies[tax] !== undefined

    if(!taxonomyToChecExists) {
        return false
    }

    const taxonomyToCheck = taxonomies[tax]

    return taxonomyToCheck.findIndex((taxObj) => {
        return taxObj.term_id === value
    }) !== -1
}

export function postInSelection(filterSelection, post) {
    const taxonomyName = filterSelection[0]
    // noinspection JSCheckFunctionSignatures
    const taxonomyValue = parseInt(filterSelection[1])

    return postApplysToTax(post, taxonomyName, taxonomyValue)
}