import {routerRedux} from "dva/router";
import Immutable, {update, merge} from 'immutable';
import {trim, merge as mergeLodash, difference} from 'lodash'
import {error, fetchAdmin, fetchKeepAlive, handleResponse, indexArrayByKey} from '../../services/_sk/api';
import * as sk from "@/services/_sk/api";

function arrToMap(arr, key) {
  const indexedItems = {};

  arr.forEach(val => {
    indexedItems[val[key]] = val;
  });

  return indexedItems;
}

// удержание сессии в живых
setInterval(() => {
  fetchKeepAlive();
}, 900000); // Каждые 15 мин

export default {
  namespace: 'skGlobal',

  state: {
    tabs: Immutable.fromJS({}),
    outLayout: Immutable.fromJS({}),
    headerLayout: Immutable.fromJS({}),
    headerModuleLayout: Immutable.fromJS({}),
    leftLayout: Immutable.fromJS({}),
    logLayout: Immutable.fromJS({}),
    footerLayout: Immutable.fromJS({}),
    editingSectionForm: Immutable.fromJS({isFetchReady: false, isShowModal: false}),
    activeTab: '',
    storeInitialized: false,
    sidebarActiveItem: 'section',
    errors: [],
    dirtyForms: [],
    routerParams: {}
  },

  effects: {
    // Первичных запрос данных
    *fetchInitData({ payload }, { call, put }) {

      const dataPack = {
        skCmd: { cmd: 'fetchInitData', data: {} },
      };

      yield put({
        type: 'postDataWithUpdateAllStore',
        payload: dataPack
      });

    },

    /**
     * 1. Отправляет данные на сервер.
     * 2. Обрабатывает ответ
     * 3. Обновляет данные стора одного таба
     */
    *postData4Tabs({ payload }, { call, put }) {

      const response = yield call(fetchAdmin, payload);
      const data = response.data || [];

      if ( handleResponse(data) ){

        const { onSuccess } = payload;

        if (onSuccess){
          onSuccess();
        }

        yield put({
          type: 'updateTabContent',
          payload: data,
        });

      }

    },

    /**
     * 1. Отправляет данные на сервер.
     * 2. Обрабатывает ответ
     * 3. Обновляет всего стора
     */
    *postDataWithUpdateAllStore({ payload }, { call, put }) {

      const response = yield call(fetchAdmin, payload);
      const responseData = response.data || [];

      if (handleResponse(responseData)){
        yield put({
          type: 'loadDataInStore',
          payload: responseData
        });
      }

    },

    *fireEvent({ payload }, { put }) {

      const {path, cmd, params} = payload;

      const dataPack = {
        [path]: {
          cmd,
          params,
        },
        skCmd: { cmd: 'fireEvent', data: {} },
      };

      yield put({
        type: 'postData4Tabs',
        payload: dataPack
      });

    },

    *updateTabContent({ payload }, { put }) {

      const data = payload;

      // Обновляем стор
      yield put({
        type: 'queryOneTab',
        payload: Immutable.fromJS(data[0]),
      });

      // Запускаем события
      yield put({
        type: 'fireEvents',
        payload: {
          data
        }
      })

    },

    *fireEvents({ payload }, { put, select }) {

      const {data} = payload;

      let aEventsToAction = {};
      let reloadTabs = false;

      // Стандартные обработчики
      const standardHandlers = {
        reload: () => {
          window.location.reload();
        },
        // reload_tree: () => {
        //   window.location.reload();
        // },
        reload_section: () => {
          reloadTabs = true;
        }
      };

      data.forEach( dataItem => {
        if ( dataItem.listenEvents ){
          const eventToAction4Module = {
            [dataItem.path]: dataItem.listenEvents
          };
          aEventsToAction = {...aEventsToAction, ...eventToAction4Module};
        }
      } );

      // Посылки с событиями
      const packages = [];

      data.forEach((dataItem) => {

        const {path} = dataItem;

        if ( dataItem.fireEvents ){

          dataItem.fireEvents.forEach( eventDataItem => {
            const eventName = eventDataItem[0];
            const eventData = eventDataItem[1];

            // есть стандартный обработчик ?
            if ( standardHandlers[eventName] !== undefined ){
              standardHandlers[eventName]();
            } else if (aEventsToAction[path] !== undefined && aEventsToAction[path][eventName] !== undefined ) {

              const cmd = aEventsToAction[path][eventName];

              packages.push({
                path, // 'out.tabs.tools_Utils';
                cmd, // 'reindex'
                params: [eventData] // [ {taskId: 1851} ]
              });

            }

          } );

        } // end if

      });


      // Запуск событий
      for (const item of packages) {
        yield put({
          type: 'fireEvent',
          payload: item
        });
      }

      if (reloadTabs) {
        const itemId = yield select(state => state.skGlobal.routerParams.leftPanelItemId);
        const module = yield select(state => state.skGlobal.routerParams.leftPanelItemName);
        yield put({
          type: 'loadTabs',
          payload: {
            path: 'out.tabs',
            itemId,
            module,
          }
        });
      }

    },

    *changePosition({ payload }, { call, put, select }) {

      const {
        path,
        dropKey,
        dragKey,
        dropPosition
      } = payload;

      let direction;
      if (dropPosition === 1) {
        direction = 'after';
      } else if (dropPosition === -1) {
        direction = 'before';
      } else {
        direction = 'append';
      }

      const dataPack = {
        [path]: {
          cmd: 'changePosition',
          direction,
          itemId: dragKey,
          overId: dropKey
        },
        skCmd: {
          cmd: 'changePosition',
          data: {
            direction,
            itemId: dragKey,
            overId: dropKey
          }
        },
      };

      const response = yield call(fetchAdmin, dataPack);

      const responseData = response.data || [];

      if (handleResponse(responseData)){
        yield put({
          type: 'changePositionReducer',
          payload
        });

        const currentId = yield select(state => state.skGlobal.routerParams.leftPanelItemId);
        // если переносится открытый раздел, то обновляем табы чтоб обновленный урл раздела
        if (dragKey === currentId) {
          const module = yield select(state => state.skGlobal.routerParams.leftPanelItemName);

          // Обновляем табы
          yield put({
            type: 'loadTabs',
            payload: {
              path: 'out.tabs',
              itemId: currentId,
              module,
            }
          });
        }
      }

    },

    *sortTableItems({ payload }, { call, put, select }) {

      const {
        path,
        dragItem,  // данные перетаскиваемого элемента
        hoverItem, // данные элемента с которым меняемся местами
        direction,  // направление
        cmdCommand
      } = payload;

      const serviceData = yield select(
        store => store.skGlobal.tabs.getIn([path, 'params', 'serviceData']).toJS()
      );

      const dataPack = {
        [path]: {
          ...serviceData,
          cmd: cmdCommand,
          data: dragItem,
          dropData: hoverItem,
          position: direction,
        }
      };

      const response = yield call(fetchAdmin, dataPack);
      const responseData = response.data || [];

      if (handleResponse(responseData)) {
        yield put({
          type: 'updateTabContent',
          payload: responseData,
        });
      }
    },

    // Первичных запрос данных
    *getSubItems({ payload }, { call, put }) {
      const { path, node, cmd } = payload;

      const dataPack = {
        skCmd: { cmd: 'getSubItems', data: { path, node } },
        [path]: {
          cmd,
          node,
        },
      };

      const response = yield call(fetchAdmin, dataPack);
      const data = response.data || [];

      if ( handleResponse(data) ){
        data[0].params.items = arrToMap(data[0].params.items, 'id');

        yield put({
          type: 'loadSubItems',
          payload: {
            data: Immutable.fromJS(data[0]),
            path
          },
        });
      }

    },

    *getTree({ payload }, { call, put }) {
      const { path, sectionId } = payload;

      const dataPack = {
        skCmd: { cmd: 'getTree', data: { path, sectionId } },
        [path]: {
          cmd: 'getTree',
          sectionId
        },
      };

      const response = yield call(fetchAdmin, dataPack);
      const data = response.data || [];

      if ( handleResponse(data) ){
        data[0].params.items = arrToMap(data[0].params.items, 'id');

        yield put({
          type: 'loadSections',
          payload: Immutable.fromJS(data[0]),
        });
      }

    },

    *forgotPassword({ payload }, { call, put }) {const { path } = payload;

      const dataPack = {
        skCmd: { cmd: 'forgotPass', data: { path } },
        [path]: {
          cmd: 'forgotPass',
        },
      };

      yield put({
        type: 'postDataWithUpdateAllStore',
        payload: dataPack
      });

    },

    *logout({ payload }, { call, put }) {

      const { path } = payload;

      const dataPack = {
        skCmd: { cmd: 'logout', data: { path } },
        [path]: {
          cmd: 'logout',
        },
      };

      yield put({
        type: 'postDataWithUpdateAllStore',
        payload: dataPack
      });

    },

    *checkForgot({ payload }, { call, put }) {

      const {path, login, captcha} = payload;

      const dataPack = {
        skCmd: { cmd: 'CheckForgot', data: { path, login, captcha } },
        [path]: {
          cmd: 'CheckForgot',
          captcha,
          login
        },
      };

      const response = yield call(fetchAdmin, dataPack);
      const responseData = response.data || [];
      const responseSuccess = responseData[0] && responseData[0].params !== undefined && responseData[0].params.success
        ? responseData[0].params.success
        : false;

      if (handleResponse(responseData)){
        yield put({
          type: 'loadDataInStore',
          payload: responseData
        });
      }

      if (!responseSuccess) {
        return Promise.reject(response);
      }

      return Promise.resolve(true);
    },

    *login({ payload }, { call, put }) {
      const { path, login, pass } = payload;

      const dataPack = {
        skCmd: { cmd: 'login', data: { path, login, pass } },
        [path]: {
          cmd: 'login',
          login,
          pass
        },
      };

      yield put({
        type: 'postDataWithUpdateAllStore',
        payload: dataPack
      });

    },

    *checkTokenForChangePassword({ payload }, { call, put }) {
      const {path, token} = payload;

      const dataPack = {
        skCmd: {cmd: 'newPassForm', data: {path, token}},
        [path]: {
          cmd: 'newPassForm',
          token
        },
      };

      yield put({
        type: 'postDataWithUpdateAllStore',
        payload: dataPack
      });
    },

    *recoveryPass({ payload }, { call, put }) {
      const {path, token, password, wpassword} = payload;

      const dataPack = {
        skCmd: {cmd: 'recoveryPass', data: {path, token, password, wpassword}},
        [path]: {
          cmd: 'recoveryPass',
          token,
          password,
          wpassword
        },
      };

      yield put({
        type: 'postDataWithUpdateAllStore',
        payload: dataPack
      });
    },

    *setLang({ payload }, { call }) {

      const { path, lang } = payload;

      const dataPack = {
        skCmd: { cmd: 'setLang', data: { path, lang } },
        [path]: {
          cmd: 'setLang',
          lang
        },
      };

      const response = yield call(fetchAdmin, dataPack);
      const responseData = response.data || [];

      if ( handleResponse(responseData) ){
        //todo Обработка fireEvents или простой релоад
        window.location.reload();
      }

    },

    *setAdminMode({ payload }, { call }) {

      const { path, mode } = payload;

      const dataPack = {
        skCmd: { cmd: 'setAdminMode', data: { path, lang } },
        [path]: {
          cmd: 'setAdminMode',
          mode
        },
      };

      const response = yield call(fetchAdmin, dataPack);
      const responseData = response.data || [];

      if ( handleResponse(responseData) ){
        window.location.reload();
      }

    },

    *setCacheMode({payload}, {call, put}) {
      const {path} = payload;

      const dataPack = {
        skCmd: {cmd: 'setCacheMode', data: {path, lang}},
        [path]: {
          cmd: 'setCacheMode',
        },
      };

      const response = yield call(fetchAdmin, dataPack);
      const responseData = response.data || [];
      if (handleResponse(responseData)){
        yield put({
          type: 'loadDataInStore',
          payload: responseData
        });
      }
    },

    *setDebugMode({payload}, {call, put}) {
      const {path} = payload;

      const dataPack = {
        skCmd: {cmd: 'setDebugMode', data: {path, lang}},
        [path]: {
          cmd: 'setDebugMode',
        },
      };

      const response = yield call(fetchAdmin, dataPack);
      const responseData = response.data || [];
      if (handleResponse(responseData)){
        window.location.reload();
      }
    },

    *setCompressionMode({ payload }, { call }) {

      const { path, mode } = payload;

      const dataPack = {
        skCmd: { cmd: 'setCompressionMode', data: { path, lang } },
        [path]: {
          cmd: 'setCompressionMode',
          mode
        },
      };

      const response = yield call(fetchAdmin, dataPack);
      const responseData = response.data || [];

      if ( handleResponse(responseData) ){
        window.location.reload();
      }

    },

    *search({ payload }, { call, put }) {

      const { path, query } = payload;

      const dataPack = {
        skCmd: { cmd: 'search', data: { path, query } },
        [path]: {
          cmd: 'search',
          data: {
            query
          }
        },
      };

      yield put({
        type: 'postDataWithUpdateAllStore',
        payload: dataPack
      });

    },


    *dropCache({ payload }, { call, put }) {

      const { path } = payload;

      const dataPack = {
        skCmd: { cmd: 'dropCache', data: { path } },
        [path]: {
          cmd: 'dropCache',
        },
      };

      yield put({
        type: 'postDataWithUpdateAllStore',
        payload: dataPack
      });

    },

    *extendedTree({ payload }, { put }) {

      yield put({
        type: 'mergeExtendedItems',
        payload,
      });
    },

    // Первичных запрос данных
    *deleteSection({ payload }, { call, put, select }) {
      const { path, sectionId } = payload;

      const dataPack = {
        skCmd: { cmd: 'deleteSection', data: { path, sectionId } },
        [path]: {
          cmd: 'deleteSection',
          sectionId,
        },
      };

      const response = yield call(fetchAdmin, dataPack);
      const data = response.data || [];

      if (handleResponse(data)){

        // Запоминаем ид родительского раздела
        const parentSectionId = yield select(
          store => store.skGlobal.leftLayout.getIn([path, 'params', 'items', sectionId.toString(), 'parent' ])
        );

        yield put({
          type: 'reducerDeleteSection',
          payload: Immutable.fromJS(data[0]),
        });

        const lastActiveItemId = yield select(
          store => store.skGlobal.leftLayout.getIn([path, 'lastActiveItemId'])
        );

        // Проверить совпадают ли активный раздел с удалённым разделом
        if ( parseInt(data[0].params.deletedId) === parseInt(lastActiveItemId)){

          // Переходим в род.раздел
          yield put(
            routerRedux.push({
              pathname: `/${path}=${parentSectionId}`
            })
          );
        }

      }

    },

    // Первичных запрос данных
    *getForm({ payload }, { call, put }) {
      const { path, item } = payload;

      const dataPack = {
        skCmd: { cmd: 'getForm', data: { path, itemId: item.id || 0, parent: item.parent } },
        [path]: {
          cmd: 'getForm',
          selectedId: item.id || 0,
          itemId: item.id || 0,
          item,
        },
      };

      const response = yield call(fetchAdmin, dataPack);
      const data = response.data || [];

      if ( handleResponse(data) ){
        yield put({
          type: 'reducerFetchFormEditingSection',
          payload: Immutable.fromJS(data[0]),
        });
      }

    },

    // Сохранение отредактированного раздела
    *saveSection({ payload }, { call, put, select }) {
      const { path, item } = payload;
      const dataPack = {
        skCmd: { cmd: 'saveSection', data: { item, sectionId: parseInt(item.id) } },
        [path]: {
          cmd: 'saveSection',
          sectionId: parseInt(item.id),
          item
        },
      };

      const response = yield call(fetchAdmin, dataPack);
      const responseData = response.data || [];

      if ( handleResponse(responseData) ){
        const data = response.data[0] || [];

        data.params.item.children = [];
        yield put({
          type: 'reducerSaveSection',
          payload: Immutable.fromJS(data),
        });

        const bIsNewGood = !item.id;
        if ( bIsNewGood ){
          yield put({
            type: 'skGlobal/setAddedSectionId',
            payload: {
              path,
              itemId: data.params.item.id
            }
          });
        }

        // Активная вкладка сайдбара
        const sidebarActiveItem = yield select(state => state.skGlobal.sidebarActiveItem);
        // Имя модуля
        const module = yield select(state => state.skGlobal.routerParams.leftPanelItemName);
        // Id редактируемого раздела
        const itemId = data.params.item.id;

        // Переход в этот раздел
        yield put(
          routerRedux.push({
            pathname: `/out.left.${sidebarActiveItem}=${itemId}`
          })
        );

        // Обновляем табы
        yield put({
          type: 'loadTabs',
          payload: {
            path: 'out.tabs',
            itemId,
            module,
          }
        });
      }
    },

    *loadTabs({ payload }, { call, put, select }) {
      const { path, itemId, module } = payload;

      const dirtyForm = yield select(state => state.skGlobal.dirtyForms);

      const loadTabsWithoutModal = () => {
        window.g_app._store.dispatch({
          type: 'skGlobal/loadTabsWithoutModal',
          payload: {
            path: path,
            itemId,
            module,
          },
        });
      };

      if ( dirtyForm.length ){
        sk.showModal(
          <div dangerouslySetInnerHTML={{ __html: sk.dict('editorCloseConfirmHeader') }} />,
          <div dangerouslySetInnerHTML={{ __html: sk.dict('editorCloseConfirm') }} />,
          loadTabsWithoutModal
        );
      } else {
        loadTabsWithoutModal();
      }

    },

    *loadTabsWithoutModal({ payload }, { call, put, select }) {
      const { path, itemId, module } = payload;

      // Запращиваем текущйи активный таб из стора
      let activeTab = yield select(state => state.skGlobal.activeTab);

      // Формируем поссылку для запроса набора вкладок
      const dataPack = {
        skCmd: { cmd: 'loadTabs', data: { itemId, module, tab: activeTab } },

        [path]: {
          cmd: 'loadTabs',
          itemId: isNaN(Number(itemId))? itemId : Number(itemId),
          module,
          tab: activeTab,
        },
      };

      const response = yield call(fetchAdmin, dataPack);
      const responseData = response.data || [];

      if ( handleResponse(responseData) ){
        // Исключаем все данные, которые не относятся к вкладкам табов
        const tabsData = responseData.filter(val => val.path.indexOf('out.tabs.') !== -1 );

        // индексируем по пути
        const tabsDataIndexedByPath = arrToMap(tabsData, 'path');

        // Сохраняем табы в стор
        yield put({
          type: 'tabsDataSaveInStore',
          payload: Immutable.fromJS(tabsDataIndexedByPath),
        });

        let routerParams = yield select(state => state.skGlobal.routerParams);
        const { init_tab, tabsItemName } = routerParams;

        // Имя запрашиваемого таба = если через урл передан параметр init_tab, то берем его
        // иначе берем имя влкадку совпадающую с именем текущей активной вкладки
        // или первую вкладку полученную из результата запроса loadTabs
        let nameFetchedTab = '';

        if ( init_tab ){
          nameFetchedTab = init_tab;
        } else {

          if ( tabsData.some(item => item.path.substr(tabsData[0].path.lastIndexOf('.') + 1) === tabsItemName) ){
            nameFetchedTab = tabsItemName;
          } else {
            nameFetchedTab = tabsData[0].path.substr(tabsData[0].path.lastIndexOf('.') + 1);
          }

        }

        // Запрос на получение данных вкладки
        yield put({
          type: 'fetchOneTab',
          payload: { activeKey: `out.tabs.${nameFetchedTab}` }
        });

        // Очишаем список грязных форм
        yield put({
          type: 'clearDirtyFormsList',
          payload: {}
        });
      }

    },

    *handleClickOnButton({ payload }, { call, put, select }) {
      const { path, action, formData='', from='', buttonData, onSuccess } = payload;
      const addParams = (buttonData && buttonData.addParams) ? buttonData.addParams : {};
      const skipData = (buttonData && buttonData.skipData !== undefined) ? buttonData.skipData : false;
      const tab = yield select(state => state.skGlobal.tabs.get(path));

      const storeServiceData = tab.get('params');
      const serviceData = storeServiceData.get('serviceData');

      // const confirmText = button.get('confirmText') || '';
      // const unsetFormDirtyBlocker = button.get('unsetFormDirtyBlocker');
      const dataPack = {};
      if (action) {
        dataPack.cmd = action;

        let componentData = {};

        if (!skipData) {
          if (formData) {
            componentData = {
              data: formData,
            };
          }

          if (from){
            dataPack.from = from;
          }

        }
        const data4Sending = {
          skCmd: { cmd: 'handleClickOnButton', data: { action, path } },
          onSuccess,
          [path]: mergeLodash(
            dataPack,
            serviceData.toJS(),
            addParams,
            componentData
          )
        };

        // Удаляем запись из списка грязных форм
        yield put({
          type: 'deleteDirtyFormFromDirtyList',
          payload: {
            path: path
          }
        });

        yield put({
          type: 'postData4Tabs',
          payload: data4Sending
        });

      }

      // иначе попробовать вызвать состояние компонента
      // else if (state) {
        // данные
        // let data = {
        //   path: container.path,
        //   serviceData: serviceData,
        //   addParams: Ext.merge(dataPack, serviceData, addParams)
        // };
        //
        // // выполнить состояние компонента
        // container.nowComponent.execute( data, state );
      // }
    },

    /**
     * Обработчик кастомных кнопок в строке таблицы "Параметры"
      */
    *handleCustomButtonInParameters({ payload }, { call, put }) {
      const { path, data, dispatchCmd } = payload;

        const data4Sending = {
          skCmd: { cmd: dispatchCmd, data: {} },
          [path]: {
            ...data
          },
        };

        yield put({
          type: 'postData4Tabs',
          payload: data4Sending
        });

    },

    /** Удаление файлов библиотек */
      *deleteFileItems({ payload }, { call, put }) {

      const { path, data } = payload;

      const {cmd} = data;

      const data4Sending = {
        skCmd: { cmd, data: { path } },
        [path]: data,
      };

      yield put({
        type: 'postData4Tabs',
        payload: data4Sending
      });

    },

    /** Удаление файлов библиотек */
    *filesAddForm({ payload }, { call, put }) {

      const { path } = payload;

      const cmd = 'addForm';

        const data4Sending = {
          skCmd: { cmd, data: { path } },
          [path]: {
            cmd
          },
        };

      yield put({
        type: 'postData4Tabs',
        payload: data4Sending
      });

    },

    /** Удаление файлов библиотек */
    *updateListFiles({ payload }, { put }) {

      const data = payload;

      if ( handleResponse(data) ){
        yield put({
          type: 'updateTabContent',
          payload: data,
        });
      }

    },

    *handleClickOnButtonTable({ payload }, { call, put, select }) {
      const { path, cmd, data } = payload;

      const tab = yield select(state => state.skGlobal.tabs.get(path));

      const serviceData = tab.getIn(['params', 'serviceData']);
      const addParams = data.addParams || {};

      const data4Sending = {
        skCmd: { cmd: 'handleClickOnButtonTable', data: { cmd, path } },
        [path]: {
          ...serviceData.toJS(),
          ...addParams,
          from: 'list',
          cmd,
          data,
        },
      };

      yield put({
        type: 'postData4Tabs',
        payload: data4Sending
      });

    },

    *handleFilterButton({ payload }, { call, put, select }) {
      const { addParams, path } = payload;

      const tab = yield select(state => state.skGlobal.tabs.get(path));

      const serviceData = tab.getIn(['params', 'serviceData']);

      const data4Sending = {
        skCmd: { cmd: 'handleFilterButton', data: { addParams, path } },
        [path]: {
          ...serviceData.toJS(),
          ...addParams,
        },
      };

      yield put({
        type: 'postData4Tabs',
        payload: data4Sending
      });

    },

    *handleFilterData({ payload }, { call, put, select }) {
      const { path, page, values } = payload;

      const tab = yield select(state => state.skGlobal.tabs.get(path));

      const serviceData = tab.getIn(['params', 'serviceData']);

      const data4Sending = {
        skCmd: { cmd: 'handleFilterData', data: { path, page } },
        [path]: {
          from: 'list',
          ...serviceData.toJS(),
          ...values,
          page,
        },
      };

      yield put({
        type: 'postData4Tabs',
        payload: data4Sending
      });

    },

    *saveFieldTableFromList({ payload }, { call, put, select }) {
      const { path, cmd, data, fieldName, from } = payload;

      const tab = yield select(state => state.skGlobal.tabs.get(path));

      const serviceData = tab.getIn(['params', 'serviceData']);

      const data4Sending = {
        skCmd: { cmd: 'saveFieldTableFromList', data: { path, fieldName } },
        [path]: {
          ...serviceData.toJS(),
          from,
          cmd,
          data,
          field_name: fieldName,
        },
      };

      yield put({
        type: 'postData4Tabs',
        payload: data4Sending
      });

    },

    *fetchOneTab({ payload }, { call, put, select }) {
      const { activeKey } = payload;

      const tabs = yield select(state => state.skGlobal.tabs);
      const routerParams = yield select(state => state.skGlobal.routerParams);
      const { init_param } = routerParams;

      // Если стоит флаг инициализации вкладки
      if ( tabs.getIn([activeKey, 'params', 'initTabFlag']) ) {

        const data = {
          cmd: 'init',
          ...tabs.getIn([activeKey, 'params', 'serviceData']).toJS(),
        };

        if (init_param){
          data.init_param = init_param;
        }

        const dataPack = {
          skCmd: { cmd: 'fetchOneTab', data: { key: activeKey } },
          [activeKey]: data,
        };

        yield put({
          type: 'postData4Tabs',
          payload: dataPack
        });

      }


      const activeTab = activeKey.substr(activeKey.lastIndexOf('.') + 1);

      yield put({
        type: 'changeActiveTab',
        payload: { activeTab }
      })

    },

    *updateForm({ payload }, { call, put, select }) {
      const { path, changedValues, values } = payload;

      const storeModuleData = yield select(state => state.skGlobal.tabs.get(path));

      const storeServiceData = storeModuleData.getIn(['params', 'serviceData']);
      const storeFields = storeModuleData.getIn(['params', 'items'], []);

      for (const nameField in changedValues) {
        if (Object.prototype.hasOwnProperty.call(changedValues, nameField)) {
          const index = storeFields.findIndex(val => val.get('name') === nameField);

          if (index !== -1) {
            const storeField = storeFields.get(index);
            const updateAction = storeField.get('onUpdateAction');
            const fieldValue = changedValues[nameField].value;
            const fieldOldValue = storeField.get('value');

            yield put({
              type: 'updateForm2Param',
              payload,
            });

            // если есть onUpdateAction
            if (updateAction) {
              // данные к отправке
              const dataPack = {
                skCmd: {
                  cmd: updateAction,
                  data: { path: storeModuleData.get('path'), nameField },
                },
                [storeModuleData.get('path')]: {
                  ...storeServiceData.toJS(),
                  ...{
                    cmd: updateAction,
                    from: 'form',
                    fieldName: nameField,
                    fieldValue,
                    fieldOldValue,
                    formData: values,
                  },
                },
              };

              if (fieldValue !== fieldOldValue) {
                yield put({
                  type: 'postData4Tabs',
                  payload: dataPack
                });
              }
            }
          }
        }
      }
    },
  },

  reducers: {
    tabsDataSaveInStore(state, action) {
      return {
        ...state,
        tabs: action.payload,
      };
    },

    /**
     * Загружает информацию об ошибках в стор
     */
    loadErrorInStore(state, action) {
      return {
        ...state,
        errors: [...state.errors, action.payload.message]
      };
    },

    clearErrors(state) {
      return {
        ...state,
        errors: [],
      };
    },

    // Очистка списка грязных форм
    clearDirtyFormsList(state) {
      return {
        ...state,
        dirtyForms: [],
      };
    },

    // удаление формы из списка грязных форм
    deleteDirtyFormFromDirtyList(state, action){
      const {path} = action.payload;
      return {
        ...state,
        dirtyForms: difference(state.dirtyForms, [path])
      }
    },

    // обновление списка грязных форм
    updateDirtyFormsList(state, action) {

      const { formName, isDirty } = action.payload;

      const storeDirtyForms = state.dirtyForms;

      let actualDirtyForms = [
        ...storeDirtyForms
      ];

      if ( isDirty ){
        if ( !storeDirtyForms.includes(formName) ){
          actualDirtyForms = [
            ...storeDirtyForms,
            formName
          ];
        }
      } else {

        if ( storeDirtyForms.includes(formName) ){
          // удалить из store
          actualDirtyForms = _.difference(storeDirtyForms, [formName]);
        }

      }

      return {
        ...state,
        dirtyForms: actualDirtyForms,
      };
    },

    mergeExtendedItems(state, action) {

      const {payload} = action;

      const {expandedKeys, path} = payload;

      return {
        ...state,
        leftLayout: state.leftLayout.setIn(
          [path, 'params', 'parents'],
          Immutable.List(expandedKeys)
        )
      };
    },

    /** Устанавливает значение флага "ид добавленного раздела" */
    setAddedSectionId(state, action) {

      const {payload} = action;

      const {path, itemId} = payload;

      return {
        ...state,
        leftLayout: state.leftLayout.setIn(
          [path, 'addedSectionId'],
          itemId
        )
      };
    },

    /** Очищаем значение флага "ид добавленного раздела" */
    clearAddedSectionId(state, action) {

      const {payload} = action;

      const {path} = payload;

      return {
        ...state,
        leftLayout: state.leftLayout.setIn(
          [path, 'addedSectionId'],
          0
        )
      };
    },

    saveRouterParams(state, action) {

      const {payload} = action;

      // const {pathname, leftPanelItemName, leftPanelItemId, tabsItemName} = payload;
      const {pathname} = payload;

      const params = {};

      const path = trim(pathname, '/');

      path.split(';').forEach((item) => {

        const [name, value] = item.split('=');

        if ( name.startsWith('out.left') ){
          const regex4SectionName = /out\.left\.([^=]*)/gm;
          const result4SectionName = regex4SectionName.exec(name);
          params.leftPanelItemName = result4SectionName[1];
          params.leftPanelItemId = value;

          // params.sectionName = value;
        } else if (name.startsWith('out.tabs')){
          params.tabsItemName = value;
        } else {
          params[name] = value;
        }

      });

      const {leftPanelItemName, leftPanelItemId} = params;

      let newState = {
        ...state
      };

      newState = {
        ...newState,
        routerParams: params
      };

      if ( leftPanelItemName ){
        newState = {
          ...newState,
          sidebarActiveItem: leftPanelItemName,
        }
      }

      if ( leftPanelItemId ){
        newState = {
          ...newState,
          leftLayout: state.leftLayout.setIn(
            [`out.left.${leftPanelItemName}`, 'lastActiveItemId'],
            leftPanelItemId
          ),
        }
      }

      return newState;

    },

    loadDataInStore(state, action){

      const {payload} = action;

      // Исключаем все данные, которые не относятся к выбранной зоне
      const dataOut = payload.filter(val => val.path === 'out' );
      const dataLeft = payload.filter(val => val.path.indexOf('out.left.') !== -1 );
      const dataHeaderModule = payload.filter(val => (val.path === 'out.header') );
      const dataHeader = payload.filter(val => (val.path.indexOf('out.header.') !== -1) );
      const dataTabs = payload.filter(val => val.path.indexOf('out.tabs.') !== -1 );
      const dataLogs = payload.filter(val => val.path.indexOf('out.log') !== -1 );
      const dataFooter = payload.filter(val => val.path.indexOf('out.footer') !== -1 );

      // индексируем по пути
      const dataOutIndexed = arrToMap(dataOut, 'path');
      const dataLeftIndexed = arrToMap(dataLeft, 'path');
      const dataHeaderModuleIndexed = arrToMap(dataHeaderModule, 'path');
      const dataHeaderIndexed = arrToMap(dataHeader, 'path');
      const dataTabsIndexed = arrToMap(dataTabs, 'path');
      const dataLogsIndexed = arrToMap(dataLogs, 'path');
      const dataFooterIndexed = arrToMap(dataFooter, 'path');

      // Доп. обработка
      Object.entries(dataLeftIndexed).forEach(([name, value]) => {
        if (value.moduleName === 'Tree'){
          value.params.items = arrToMap(value.params.items, 'id');
        }
      });

      return {
        ...state,
        outLayout: state.outLayout.merge(Immutable.fromJS(dataOutIndexed)),
        logLayout: state.logLayout.merge(Immutable.fromJS(dataLogsIndexed)),
        footerLayout: state.footerLayout.merge(Immutable.fromJS(dataFooterIndexed)),
        leftLayout: Immutable.fromJS(dataLeftIndexed).mergeDeep(state.leftLayout),
        headerModuleLayout: state.headerModuleLayout.merge(Immutable.fromJS(dataHeaderModuleIndexed)),
        headerLayout: state.headerLayout.merge(Immutable.fromJS(dataHeaderIndexed)),
        tabs: state.tabs.merge(Immutable.fromJS(dataTabsIndexed)),
        storeInitialized: true
      };

    },


    queryLeftLayout(state, action) {
      // todo Перенести индексацию к сайд-эффектам

      Object.entries(action.payload).forEach(([name, value]) => {
        if (value.moduleName === 'Tree'){
          value.params.items = arrToMap(value.params.items, 'id');
        }
      });

      return {
        ...state,
        leftLayout: Immutable.fromJS(action.payload),
        storeInitialized: true
      };
    },

    updateForm2Param(state, action) {
      const { path, fieldName, fieldValue, changedValues } = action.payload;
      const storeItems = state.tabs.getIn([path, 'params', 'items']);

      const newItems = update(
        storeItems,
        storeItems.findIndex(val => val.get('name') === fieldName),
        (item) => {
          return merge(item, {
            value: fieldValue,
            touched: (changedValues !== undefined && changedValues[fieldName]['touched'] !== undefined)
              ? changedValues[fieldName]['touched']
              : false
          })
        }
      );

      return {
        ...state,
        tabs: state.tabs.setIn([path, 'params', 'items'], newItems),
      };
    },

    reducerDeleteSection(state, action) {
      const { payload } = action;

      const path = payload.get('path');

      const cmd = payload.getIn(['params', 'cmd']);
      const handledItemId = payload.getIn(['params', 'deletedId']);

      if (cmd === 'deleteSection' && handledItemId) {
        const storeItems = state.leftLayout.getIn([path, 'params', 'items']);

        return {
          ...state,
          leftLayout: state.leftLayout.setIn(
            [path, 'params', 'items'],
            storeItems.filter((value) => {
              // Это удаляемая запись ?
              const bIsDeletableItem = (parseInt(value.get('id')) === handledItemId);
              // Это дочерняя запись удаляемой записи ?
              const bIsDeletableSubItem = (parseInt(value.get('parent')) === handledItemId);
              return (!(bIsDeletableItem || bIsDeletableSubItem));
            })
          ),
        };

      }

      return state;
    },

    reducerFetchFormEditingSection(state, action) {
      const {editingSectionForm} = state;
      const {payload} = action;
      const cmd = payload.getIn(['params', 'cmd']);

      if (cmd !== 'createForm') {
        return state;
      }

      return {
        ...state,
        editingSectionForm: payload
          .set('isFetchReady', true)
          .set('isShowModal', editingSectionForm.get('isShowModal')),
      };
    },

    toggleModalFormEditingSection (state, action) {
      const {payload} = action;
      const {isFetchReady, isShowModal} = payload;

      let {editingSectionForm} = state;

      if (isFetchReady !== undefined) {
        editingSectionForm = editingSectionForm.set('isFetchReady', isFetchReady)
      }

      if (isShowModal !== undefined) {
        editingSectionForm = editingSectionForm.set('isShowModal', isShowModal)
      }

      return {
        ...state,
        editingSectionForm
      }
    },
    // Обновляем стор после сохранения отредактированного раздела
    reducerSaveSection(state, action) {
      const { payload } = action;

      // Данные новой записи
      const newSection = payload.getIn(['params', 'item']);

      // Путь к модулю
      const modulePath = payload.getIn(['path']);

      // Возвращаем новое состояние store
      return {
        ...state,
        leftLayout: state.leftLayout.mergeIn(
          [modulePath, 'params', 'items'], {
            [newSection.get('id')]: newSection,
          }),
      };
    },
    queryOneTab(state, action) {
      const items = action.payload.getIn(['params', 'items'], null);
      const storeModel = action.payload.getIn(['params', 'storeModel'], null);
      const columnsMode = action.payload.getIn(['params', 'columnsMode'], null);
      const files = action.payload.getIn(['params', 'files'], null);
      const componentName = action.payload.getIn(['params', 'componentName'], null);
      const extComponent = action.payload.getIn(['params', 'extComponent'], null);
      const isTableComponent = state.tabs.getIn([action.payload.get('path'), 'params', 'extComponent']) === 'List';

      // Т.к. некоторые ответы сервера содержат только данные для вывода предупреждений/ошибок и не содержат
      // никакого контента, то НЕ обновляем store, если ответ не содержал данных о контенте
      if (!items && !storeModel && !columnsMode && !files && !(componentName || extComponent)) {
        return state;
      }

      if (action.payload.getIn(['params', 'cmd']) === 'loadItem') {
        let itemsInStore = state.tabs.getIn([action.payload.get('path'), 'params', 'items']);
        const itemsFromPayload = action.payload.getIn(['params', 'items']);

        if ( !isTableComponent ){
          // формы. Обновление полей формы

          itemsFromPayload.forEach(val => {
            const nameField = val.get('name');

            const index = itemsInStore.findIndex(itemStore => {
              return !!(itemStore.get('name') === nameField);
            });

            if (index !== -1) {
              itemsInStore = itemsInStore.mergeIn([index], val);
            }
          });

        } else {
          //tables
          // обновление строк таблицы

          let keyFieldArr = action.payload.getIn(['params', 'keyField']);
          if (typeof keyFieldArr === 'string') {
            keyFieldArr = [keyFieldArr];
          }
          if (!Array.isArray(keyFieldArr)) {
            keyFieldArr = keyFieldArr.toJS();
          }

          itemsFromPayload.forEach(currentItemFromPayload => {
            const itemStoreIndex = itemsInStore.findIndex(itemInStore => {
              let resultCompare = true;
              for (const keyFieldItem in keyFieldArr) {
                resultCompare = String(itemInStore.get(keyFieldArr[keyFieldItem])) === String(currentItemFromPayload.get(keyFieldArr[keyFieldItem]));
                if (!resultCompare) {
                  break;
                }
              }
              return resultCompare;
            });

            if (itemStoreIndex !== -1) {
              itemsInStore = itemsInStore.mergeIn([itemStoreIndex], currentItemFromPayload);
            }

          });

        }

        return {
          ...state,
          tabs: state.tabs.setIn([action.payload.get('path'), 'params', 'items'], itemsInStore),
        };
      }

      return {
        ...state,
        tabs: state.tabs.setIn([action.payload.get('path')], action.payload),
      };
    },

    changeActiveTab(state, action){

      const {activeTab} = action.payload;

      return {
        ...state,
        activeTab
      };

    },
    loadSubItems(state, action) {
      const { payload } = action;

      const {data, path} = payload;

      const itemsFromPayload = data.getIn(['params', 'items']);

      return {
        ...state,
        leftLayout: state.leftLayout.mergeIn(
          [path, 'params', 'items'],
          itemsFromPayload
        ),
      };
    },

    changePositionReducer(state, action) {
      const { payload } = action;

      const {
        path,
        dropKey,
        dragKey,
        dropPosition
      } = payload;

      let items = state.leftLayout.getIn([path, 'params', 'items']);

      const dropItem = items.get(dropKey);

      // выбираем номер по порядку для относительного элемента
      const dropItemPosition = dropItem.get('position');
      const dropItemParent = dropItem.get('parent');

      // перемещение до указанной позиции
      if (dropPosition === -1) {

        items.forEach((val, key) => {

          const parent = val.get('parent');
          const position = val.get('position');

          // перемещаемому назначаем ту же позицию, что у относительного
          if (key === dragKey) {
            items = items.mergeIn([key], {
              position: dropItemPosition,
              parent: dropItemParent
            });
          }

          // смещаем те, что c позицией больше или равной относительного
          else if (parent === dropItemParent && position >= dropItemPosition) {
            items = items.setIn([key, 'position'], position + 1);
          }

        });

      } else if (dropPosition === 1) {
        // перемещение после указанной позиции

        items.forEach((val, key) => {

          const parent = val.get('parent');
          const position = val.get('position');

          // перемещаемому назначаем ту же позицию, что у относительного
          if (key === dragKey) {
            items = items.mergeIn([key], {
              position: dropItemPosition + 1, // отличие "+1"
              parent: dropItemParent
            });
          }

          // смещаем те, что c позицией больше относительного
          else if (parent === dropItemParent && position > dropItemPosition) { // отличие ">" вместо ">="
            items = items.setIn([key, 'position'], position + 1);
          }

        });

      } else {
        // перемещение внутрь указанной позиции

        const hasSubsections = !!items.filter(val => {
          return val.get('parent') === Number(dropKey)
        }).size;

        // нераскрытый раздел - нет подчиненных и нет элемента children или
        // он пуст
        const children = dropItem.get('children');
        if (!hasSubsections && (!children || !children.length)) {
          // если не загружена ветка

          // убираем из вывода, будет добалвлен при подгрузке подчиненного дерева
          items = items.mergeIn([dragKey], {
            position: 0,
            parent: 0
          });

        } else {
          // если была раскрыта

          // найти максимальную позицию (если есть)
          const maxPosition = items.reduce((max, val) => {
            const parent = val.get('parent');
            const position = val.get('position');
            if (parent === Number(dropKey)) {
              return Math.max(max, position);
            }
            return max;
          }, 0);

          // если нет подчиненных - добавить 1
          items = items.mergeIn([dragKey], {
            position: maxPosition + 1,
            parent: dropKey
          });

        }
      }

      return {
        ...state,
        leftLayout: state.leftLayout.mergeIn(
          [path, 'params', 'items'],
          items
        ),
      };
    },

    loadSections(state, action) {
      const { payload } = action;

      const paramsFromPayload = payload.getIn(['params']);

      const path = payload.get('path');

      return {
        ...state,
        leftLayout: state.leftLayout.mergeIn(
          [path, 'params'],
          paramsFromPayload
        ),
      };
    },



  },

  subscriptions: {
    setup({ history, dispatch }) {
      return history.listen(({ pathname, search }) => {
        dispatch({
          type: 'saveRouterParams',
          payload: {
            pathname
          }
        });
      });
    },
  },
};
