addEventListener('DOMContentLoaded', function () {
    const cartCounterInitClass = 'cart-counter-init'

    const cartCounter = document.getElementById('cart-counter')

    let generalSyncObserver = new MutationObserver(runGeneralSync)

    function initListeners() {
        const additionButtons = document.getElementsByClassName('wc-block-components-quantity-selector__button--plus').toConnectedArray()
        const subtractionButtons = document.getElementsByClassName('wc-block-components-quantity-selector__button--minus').toConnectedArray()
        const removeButtons = document.getElementsByClassName('wc-block-cart-item__remove-link').toConnectedArray()

        const cartElements = document.getElementsByClassName('wc-block-cart-items__row').toConnectedArray()

        const costDisplayElement = document.getElementsByClassName('wc-block-components-totals-item__value').toConnectedArray()[0]

        if (cartElements.length === 0) {
            return
        }

        function addButtonEventListener(element, updateNumber) {
            if (element.classList.contains(cartCounterInitClass)) {
                return
            }

            element.addEventListener('click', function () {
                updateCurrentNumber(updateNumber, cartCounter)
            })

            element.classList.add(cartCounterInitClass)
        }

        additionButtons.forEach((element) => {
            addButtonEventListener(element, 1);
        })

        subtractionButtons.forEach((element) => {
            addButtonEventListener(element, -1);
        })

        removeButtons.forEach((element) => {
            addButtonEventListener(element, -1);
        })

        if (!costDisplayElement) {
            return
        }

        generalSyncObserver.disconnect()
        generalSyncObserver = new MutationObserver(() => runGeneralSync(cartElements))
        generalSyncObserver.observe(costDisplayElement, {childList: true, subtree: true})
    }

    initListeners();

    const contentWrapper = document.getElementById('content')
    const observerOptions = {
        childList: true,
        subtree: true,
    };

    const listenerInitMutationObserver = new MutationObserver(initListeners)
    listenerInitMutationObserver.observe(contentWrapper, observerOptions)


    /**
     * @param change {Number}
     * @param counter {HTMLElement}
     */
    function updateCurrentNumber(change, counter) {
        const currentNumber = Number.parseInt(counter.innerText)
        counter.innerText = (currentNumber + change).toString()
        console.log('updated')
    }

    function runGeneralSync(cartElements) {
        let currentCount = cartElements.length

        cartElements.forEach((element) => {
            const quantityCounterElement = getNextInnerClass(element, 'wc-block-components-quantity-selector__input')

            if (!quantityCounterElement) {
                return
            }

            currentCount -= 1
            currentCount += parseInt(quantityCounterElement.value)
        })

        cartCounter.innerText = (currentCount).toString()
    }
})

