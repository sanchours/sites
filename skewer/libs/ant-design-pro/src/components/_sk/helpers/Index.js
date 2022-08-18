// файл с хелперами в чистых функциях


// сокращение строки до символов возможно нужен, если текст не в одну строчку.
export function truncateTitle(title, number) {
	let answer = title
	if (title.length > number) {
		answer = `${title.substring(0, number - 1)}...`;
		return answer;
	}
	return title
}

// нужен ли скролл елементу
export function scrollable(element) {
	return element.scrollHeight > element.clientHeight;
}

export function vh() {
	const h = Math.max(document.documentElement.clientHeight, window.innerHeight || 0);
	return h;
}

export function addEvent(object, type, callback) {
	if (object == null || typeof (object) == 'undefined') return;
	if (object.addEventListener) {
		object.addEventListener(type, callback, false);
	} else if (object.attachEvent) {
		object.attachEvent(`on ${type}`, callback);
	} else {
		object[`on ${type}`] = callback;
	}
};