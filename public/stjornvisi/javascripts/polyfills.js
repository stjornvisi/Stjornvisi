/**
 * Created by einar on 27/01/15.
 */

var stickyElements = document.getElementsByClassName('block-item');

for (var i = stickyElements.length - 1; i >= 0; i--) {
    Stickyfill.add(stickyElements[i]);
}