import React from 'react';
import {message, Modal} from 'antd';
import { zipObject } from 'lodash'
import request from '@/utils/request';
import { renderToString } from 'react-dom/server'

export async function fetchAdmin(params) {
  const handledParams = {...params};
  delete handledParams.onSuccess;
  //console.log('send to server', handledParams);
  return request('/admin/index.php', {
    method: 'POST',
    body: {
      data: {
        ...handledParams,
      },
      layoutMode: 'Cms',
      sessionId: window.sessionId || '',
    },
  });
}

export async function fetchKeepAlive() {
  return request('/keepalive.php?ping=1', {
    method: 'GET',
  });
}

export function dict(name){
  return (window.dict !== undefined && window.dict[name]) ? window.dict[name] : name;
}

export function showModal(sHeader, sText, fAction){

  Modal.confirm({
    title: sHeader,
    content: <div dangerouslySetInnerHTML={{ __html: renderToString(sText) }} />,
    okText: dict('yes'),
    cancelText: dict('no'),
    centered: true,
    onOk: () => {
      fAction();
    },
    onCancel: () => {
      Modal.destroyAll();
    },
    okButtonProps: {
      className: "sk-main-button sk-modal-btn_ok"
    },
    cancelButtonProps: {
      className: "sk-main-button sk-main-button--white sk-modal-btn_cancel"
    }
  });

}

export function modalWarning(title, content){

  Modal.warning({
    title,
    content,
    centered: true,
    onOk: () => {
      Modal.destroyAll();
    }
  })

}

/**
 * Выдать сообщение об ошибке
 * @param header - Заголовок
 * @param text - текст ошибки
 * @param delay - длительность показа сообщения(в секундах)
 */
export function error(header, text, delay=5){

  if ( text === undefined ) {
    text = header;
    header = dict('error');
  }

  message.error(
    <React.Fragment>
      <span style={{fontSize: 16, fontWeight: 600 }} dangerouslySetInnerHTML={{ __html: header }} /> <br />
      <span style={{fontSize: 16 }} dangerouslySetInnerHTML={{ __html: text }} />
    </React.Fragment>
    ,
    delay
  );
}

export function showMessages(messages, type = 'info') {
  for (const itemId in messages) {
    if (Object.prototype.hasOwnProperty.call(messages, itemId)) {
      const item = messages[itemId];
      const text = <span style={{fontSize: 16 }} dangerouslySetInnerHTML={{ __html: `${item[0]}<br />${item[1]}` }} />;

      let time;
      if (item[2]) {
        // если задано время, использовать его
        if (item[2] < 0) {
          time = 86400000;
        } else {
          time = item[2] / 1000;
        }
      } else {
        // иначе стандартные тайминги для вывода сообщений
        if (type === 'error') {
          time = 5; // для ошибки
        } else {
          time = 2; // для остальных
        }
      }

      if (type === 'info') {
        message.info(text, time);
      } else if (type === 'success') {
        message.success(text, time);
      } else if (type === 'error') {
        message.error(text, time);
      }
    }
  }
}

/**
 * Обработка ответа от сервера.
 * Здесь будут выполняться асснхронные операции
 */
export function handleResponse(data) {

  if (data[0] && (data[0].params !== undefined) && (data[0].params.error !== undefined)){
    error(data[0].params.error);
    return false;
  }


  data.forEach(item => {

    if (item.params.moduleMessageList){
      showMessages(item.params.moduleMessageList, 'info');
    }

    if (item.params.moduleErrorList){
      showMessages(item.params.moduleErrorList, 'info');
    }

    for (const warningId in item.params.moduleWarningList){
      if (Object.prototype.hasOwnProperty.call(item.params.moduleWarningList, warningId)) {
        const warning = item.params.moduleWarningList[warningId];
        modalWarning(warning[0], warning[1]);
      }
    }

    if (item.params.pageMessages) {
      showMessages(item.params.pageMessages, 'info');
    }

    if (item.params.pageErrors) {
      showMessages(item.params.pageErrors, 'error');
    }
  });

  return true;

}

export function newWindow(href, inData) {
  const data = {
    width: '80%',
    height: '70%',
    ...inData
  };

  let w = data.width;
  let h = data.height;

  if(typeof w==='string' && w.length>1 && w.substr(w.length-1,1)==='%')
    w=parseInt(window.screen.width*parseInt(w,10)/100,10);
  if(typeof h==='string' && h.length>1 && h.substr(h.length-1,1)==='%')
    h=parseInt(window.screen.height*parseInt(h,10)/100,10);

  const top = (window.screen.height - h) / 2;
  const left = (window.screen.width - w) / 2;

  // eslint-disable-next-line no-shadow
  const newWindow = window.open(href, 'sk_popup_window', `location=no, menubar=no, scrollbars=1, toolbar=no, status = no, resizable=no, directories=no, width=${w},left=${left}, height=${h}, top=${top}`);
  newWindow.focus();

  return true;
}

/** Индексирует массив объектов по ключу key */
export function indexArrayByKey(key, arr){
  const keys = arr.map(item => item[key]);
  return zipObject(keys, arr);
}

/* Отдает имя класса переменной */
export function getVariableClass(variable) {
  return {}.toString.call(variable).slice(8, -1);
}

export function isObject(variable) {
  return this.getVariableClass(variable) === 'Object';
}
