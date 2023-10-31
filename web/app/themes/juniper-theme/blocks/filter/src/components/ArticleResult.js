import React, {useState} from "react"

const ArticleResult = ({ index, post }) => {

    return (
        <div className="container mx-auto mb-5">
            <div className={`w-full article-row grid grid-cols-12 ${index % 2 === 0 ? 'even' : 'odd'}`}>
                <div className="img-content col-span-12 sm:col-span-5">
                    <img src={post.featured_image} />
                </div>
                <div className="article-text flex flex-col items-start text-left justify-center col-span-12 sm:col-span-7">
                    <h3 className="mb-5">{post.post_title}</h3>
                    <p className="mb-5">{post.post_author} // {post.post_date} // {post.terms.map(term => term.name).join(' // ')}</p>
                    <div className="mb-10">{post.excerpt}</div>
                    <a href={post.link} className="btn-underline">weiter lesen</a>
                </div>
            </div>
        </div>
    )
}

export default ArticleResult
