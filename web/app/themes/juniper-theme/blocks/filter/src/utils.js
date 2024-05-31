import React from "react";
import FilterDropdown from "./newComponents/SingleFilterComponents/FilterDropdown";
import axios from "axios";

export function isIterable(obj) {
    // checks for null and undefined
    return typeof obj[Symbol.iterator] === 'function';
}

export function isArray(variable) {
    return Array.isArray(variable)
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

    return foundValue
        ? decodeURIComponent(foundValue.value)
        : ''
}

export function filterOptionToElement(filterOption) {
    return <FilterDropdown data={filterOption}/>
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

export function postInSelection(taxonomyName, taxonomyValue, post) {
    // noinspection JSCheckFunctionSignatures
    taxonomyValue = parseInt(taxonomyValue)

    return postApplysToTax(post, taxonomyName, taxonomyValue)
}

export function postInTextSelection(text, post) {
    return post.post_title.toLowerCase().includes(text)
        || post.excerpt.toLowerCase().includes(text)
        || post.description_text && post.description_text.toLowerCase().includes(text)
        || post.description_title && post.description_title.toLowerCase().includes(text)
        || post.subheadline && post.subheadline.toLowerCase().includes(text)
        || post.features_text && post.features_text.toLowerCase().includes(text)
        || post.areas_of_application && post.areas_of_application.toLowerCase().includes(text)
        || Object.values(post.taxonomies).some(taxonomy => taxonomy.some(term => term.name.toLowerCase().includes(text)))
}

export function postIsAvailableOnline(post) {
    return post.price != null && post.price > 0
}

export function postHasSampleAvailable(post) {
    return post.taxonomies["purchasability"]?.some(term => term.slug === 'muster-verfuegbar' || term.slug === 'sample-available-en')
}