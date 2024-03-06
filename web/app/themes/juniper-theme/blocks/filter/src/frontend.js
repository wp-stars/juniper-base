import React from "react"
import Filter from "./components/Filter"
import ReactDOM from 'react-dom'


const filterDivs = document.querySelectorAll(".filter-entry")

filterDivs.forEach(div => {
    let data = JSON.parse(div.dataset.initialData)
    const root = ReactDOM.createRoot(div)
    root.render(<Filter {...data} />)
    div.classList.remove("filter-entry")
})

