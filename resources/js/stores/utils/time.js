// utils/time.js
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import 'dayjs/locale/ru';



export function fromNow(date) {
    dayjs.extend(relativeTime);
    dayjs.locale('ru');

    return dayjs(date).fromNow();
}
