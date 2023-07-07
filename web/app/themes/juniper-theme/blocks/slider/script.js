
let slideNum = 1,
    totalNumSlides = document.querySelectorAll(".timeline-slide").length
    sliderBtn = document.getElementById("sliderScroll")
sliderBtn.addEventListener("click", () => {
    // get the next child in the parent
    if(slideNum === totalNumSlides) {
        slideNum = 1
    } else {
        slideNum++
    }
    let targetSlide = document.querySelector(`.timeline-slider [data-slide-num='${slideNum}']`)
    targetSlide.scrollIntoView({ behavior: "smooth", block: "start", inline: "nearest" })

    let allPoints = document.querySelectorAll('.timeline-point'),
        targetPoint = document.querySelector(`.timeline [data-timeline-num='${slideNum}']`)

    allPoints.forEach(point => {
        point.classList.remove('active')
    })
    targetPoint.classList.add('active')

})