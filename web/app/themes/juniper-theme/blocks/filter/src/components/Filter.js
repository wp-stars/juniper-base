import React, { useState, useEffect } from "react"
import AlternatingResult from "./AlternatingResult"
import ArticleResult from "./ArticleResult"
import axios from 'axios'

const Filter = ( data ) => {
    const [selectedFilterVals, setSelectedFilterVals] = useState([])
    const [posts, setPosts] = useState(data.posts)
    const [page, setPage] = useState(1)

    const updateFilterVals = (e, term_id) => {
        e.preventDefault()
        let shallowFilterVals = [...selectedFilterVals]
        if(shallowFilterVals.includes(term_id)) {
            shallowFilterVals = shallowFilterVals.splice(shallowFilterVals.indexOf(term_id), 1)
        } else {
            shallowFilterVals.push(term_id)
        }

        setSelectedFilterVals(shallowFilterVals)
    }

    const loadMorePosts = () => {
        setPage(page + 1)
    }

    const removeTerm = ( event, termId ) => {
        event.stopPropagation()
        let newSelectedFilterVals = [...selectedFilterVals],
            targetIndex = newSelectedFilterVals.indexOf(termId)
        newSelectedFilterVals.splice(targetIndex, 1)
        setSelectedFilterVals(newSelectedFilterVals)
    }

    useEffect(() => {
        if(!selectedFilterVals.length) return

        const queryString = selectedFilterVals.join(",")
        axios.get(`${data.restUrl}wps/v1/data?post_type=${data.postType}&${data.taxonomy}=${queryString}&page=${page}`)
            .then(res => {
                setPosts(res.data.posts)
            })
            .catch(err => {
                console.error(err)
            })
    }, [selectedFilterVals, page])

    return (
        <div className="w-full">
            <div className="filter-choices min-h-[400px] relative text-center text-white py-20">
                <div className="container mx-auto">
                    <h3 className="text-white">Filter</h3>
                    <div className="inline-flex flex-wrap justify-center">
                        {data.terms.map((term, index) => {
                            let isActive = selectedFilterVals.includes(term.term_id)
                            return (
                                <button key={index} className={`filter-btn w-fit inline-flex ${isActive ? 'active' : ''}`} type="button" onClick={(e) => updateFilterVals(e, term.term_id)}>
                                    <span className={`${isActive ? 'bg-accent' : 'bg-light'} h-full`}><img src={term.fields.svg_icon} alt="Term Icon" /></span>
                                    <span className="btn-inner">{term.name}</span>
                                    {isActive ? 
                                        <span className="remove-term" onClick={(event) => removeTerm(event, term.term_id)}>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 11 11" fill="none">
                                                <path d="M7.89258 3.23987L2.89258 8.23987" stroke="#093642" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/>
                                                <path d="M2.89258 3.23987L7.89258 8.23987" stroke="#093642" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/>
                                            </svg>
                                        </span>
                                    : null}
                                </button>
                            )
                        })}
                    </div>
                </div>
                <div className="background bg-dark"></div>
                <div className="absolute decoration left-0 top-5">
                    <svg xmlns="http://www.w3.org/2000/svg" width="89" height="1066" viewBox="0 0 89 1066" fill="none">
                        <path d="M-202 1065.32L88.3704 282.471L-176.427 -2.45403e-05L-202 1065.32Z" fill="#B4D43D" fillOpacity="0.6"/>
                    </svg>
                </div>
            </div>
            <div className="w-full relative text-center mb-10">
                {posts.map((post, index) => {

                    if(data.style === "alternating") {
                        return <AlternatingResult key={index} index={index} post={post} />
                    } 

                    if(data.style === "article") {
                        return <ArticleResult key={index} index={index} post={post} />
                    }
                    return (
                        <div key={index}>
                            <h3>{post.post_title}</h3>
                        </div>
                    )
                })}
            </div>
            <div className="w-full flex justify-center">
                <button onClick={() => loadMorePosts()} className="btn btn-primary">mehr {data.postType} zeigen</button>
            </div>
        </div>
    )
}

export default Filter