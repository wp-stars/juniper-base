addEventListener('DOMContentLoaded', () => {
    const elements = document.getElementsByClassName('lottieOnClick')

    for (const element of elements) {
        element.addEventListener('click', playLottie)
        element.classList.add('lottie-init')
    }
})
// TODO: find alternative to using jQuery in this instance (addEventListener is not calling correctly)
jQuery(document).on('filterRefreshRenderedElements', function() {
    const elements = document.getElementsByClassName('lottieOnClick')

    const elementsNotInitialized = elements.toConnectedArray().filter((element) => {
        return !element.classList.contains('lottie-init')
    })

    for (const element of elementsNotInitialized) {
        element.addEventListener('click', playLottie)
        element.classList.add('lottie-init')
    }
})

/**
 * @param root HTMLElement
 */
function getNextInnerLottie(root) {
    if(elementIsLottieElement(root)) {
        return root
    }

    const children = root.children.toConnectedArray()

    if(children.length < 1) {
        return null
    }

    return children.find(getNextInnerLottie, children)
}

function elementIsLottieElement(element) {
    return Object.hasOwn(element, '_lottie')
}

function playLottie(event) {
    const element = event.target

    const lottieElement = getNextInnerLottie(element)

    lottieElement.play()
}
