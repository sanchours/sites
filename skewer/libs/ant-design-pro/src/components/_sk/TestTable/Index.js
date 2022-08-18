import React, {Fragment, useEffect, useState} from 'react';
import {Form, Table, Tooltip} from 'antd';
import {uniqueId, difference} from 'lodash'
import { connect } from 'dva';
import _ from 'lodash';
import {List} from 'immutable'
import { DragDropContext } from 'react-dnd';
import HTML5Backend from 'react-dnd-html5-backend';
import update from 'immutability-helper';
import moment from 'moment';
import TableRow from './TableRow'
import ButtonInTableRow from './ButtonInTableRow/Index';
import TestPanel from '../TestPanel/Index';
import DragableBodyRow from './DraggableRowDecorator/Index';
import { EditableCell, EditableRow, EditableDecorator } from './EditableRowDecorator/Index';
import FilterForm from './FilterForm/Index';
import ParamsAddObjBtn from './CustomButtonsInTableRow/Params/ParamsAddObjBtn/Index';
import ParamsEditBtn from './CustomButtonsInTableRow/Params/ParamsEditBtn/Index';
import ParamsDelBtn from './CustomButtonsInTableRow/Params/ParamsDelBtn/Index';
import ApproveBtn from './CustomButtonsInTableRow/ZonesEditor/ApproveBtn/Index';
import DeleteOrCloneBtn from './CustomButtonsInTableRow/ZonesEditor/DeleteOrCloneBtn/Index';
import DelBtn from './CustomButtonsInTableRow/Languages/DelBtn/Index';
import OverrideBtn from './CustomButtonsInTableRow/Languages/OverrideBtn/Index';
import EditBtn from './CustomButtonsInTableRow/Languages/EditBtn/Index';
import StatusGroupBtn from './CustomButtonsInTableRow/Languages/StatusGroupBtn/Index';
import AuthStatusGroupBtn from './CustomButtonsInTableRow/Auth/StatusGroupBtn/Index';
import TranslateBtn from './CustomButtonsInTableRow/Languages/TranslateBtn/Index';
import ReviewApproveBtn from './CustomButtonsInTableRow/Review/ApproveBtn/Index'
import ReviewRejectBtn from './CustomButtonsInTableRow/Review/RejectBtn/Index'
import RegionsParamsCleanObjBtn from './CustomButtonsInTableRow/Regions/ParamsCleanObjBtn/Index'
import CatalogViewModificationsBtn from './CustomButtonsInTableRow/Catalog/ViewModificationsBtn/Index'
import CardEditorDelFieldBtn from './CustomButtonsInTableRow/CardEditor/DelFieldBtn/Index'
import CardEditorEditFieldBtn from './CustomButtonsInTableRow/CardEditor/EditFieldBtn/Index'
import tableSelectors from '../../../selectors/table.js'
import * as sk from "../../../services/_sk/api";


const TableComponent = (props) => {
  const { dispatch, tabKey, saveEffect, moduleData, handleClickOnButton=null } = props;

  const [selectedRowKeys, setSelectedRowKeys] = useState([]);
  const [selectedRows, setSelectedRows] = useState([]);
  const [columns, setColumns] = useState();
  const [components, setComponents] = useState();
  const [dataSource, setDataSource] = useState();
  const [editingKey, setEditingKey] = useState('');
  const [editingRow, setEditingRow] = useState(null);
  const [filterValues, setFilterValues] = useState([]);
  const [expandedRowKeys, setExpandedRowKeys] = useState([]);

  const defaultHandleClickOnButton = (selectedRows, moduleData, dispatch) => (configButton) => () => {

    const {
      action='',
      state='',
      confirmText='',
      actionText=''
    } = configButton;

    const data = {
      items: selectedRows,
      multiple: true,
    };

    if (action) {
      const data4Sending = {
        action,
        path: moduleData.get('path'),
        formData: data,
        buttonData: configButton,
        from: 'list',
        onSuccess: () => {
          // сброс выделенности строк
          setSelectedRowKeys([]);
          setSelectedRows([]);
        }
      };

      const dispatchConfig = {
        type: 'skGlobal/handleClickOnButton',
        payload: data4Sending,
      };

      switch (state) {
        case 'delete':

          let rowText = '';

          let item = [];

          if ( (typeof(data.items) === 'object')&& data.items.length == 1 )
            item = data.items[0];

          if ( item )
            rowText = item.title || item.name || '';

          if ( !rowText )
            rowText = sk.dict('delRowNoName');

          let sHeader = sk.dict('delRowHeader');

          if ( moduleData.getIn(['params', 'checkboxSelection']) ){
            if (selectedRows.length > 1){
              sHeader = sk.dict('delRowsHeader');
              rowText = sk.dict('delRowsNoName');
            }
          }

          const sText = sk.dict('delRow').replace('{0}', rowText);

          sk.showModal(<div dangerouslySetInnerHTML={{ __html: sHeader }} />, <div dangerouslySetInnerHTML={{ __html: sText }} />, () => {
            dispatch(dispatchConfig);
          });

          break;

        case 'allow_do':
          sk.showModal(<div dangerouslySetInnerHTML={{ __html: sk.dict('allowDoHeader') }} />, <div dangerouslySetInnerHTML={{ __html: actionText }} />, () => {
            dispatch(dispatchConfig);
          });

          break;

        case 'popup_window':

          const {url} = configButton;
          sk.newWindow( url );

          break;

        default:

          // Требуется подтверждение?
          if ( confirmText ) {

            sk.showModal(<div dangerouslySetInnerHTML={{ __html: sk.dict('allowDoHeader') }} />, <div dangerouslySetInnerHTML={{ __html: confirmText }} />, () => {
              dispatch(dispatchConfig);
            });

          } else {

            // Обычная отправка
            dispatch(dispatchConfig);

          }
      }

    }

  };
  const handleClickOnButtonInLeftPanel =  handleClickOnButton ? handleClickOnButton : defaultHandleClickOnButton;

  const {getModuleNameByCustomBtnName, buildCustomBtnModulePathByName} = tableSelectors;

  const handleSave = (row, fieldName, listSaveCmd) => {

    const data4Sending = {
      path: moduleData.get('path'),
      cmd: listSaveCmd,
      from: 'field',
      data: row,
      fieldName,
    };

    dispatch({
      type: 'skGlobal/saveFieldTableFromList',
      payload: data4Sending,
    });

    setEditingKey('');
  };

  const getColumnValue = (record, value) => {
    return (value.get('jsView') === 'check' && !value.get('listSaveCmd'))
      ? record[value.get('dataIndex')] ? '+' : '-'
      : record[value.get('dataIndex')];
  };

  const initFilterValues = () => {

    const out = {};

    const filters = moduleData.getIn(['params', 'barElements']);

    filters.toJS().forEach(filter => {
      switch (filter.libName) {
        case 'Ext.Builder.ListFilterText':
          out[filter.fieldName] = filter.fieldValue;

          break;

        case 'Ext.Builder.ListFilterSelect':
          const checkedIndex = filter.menu.items.findIndex(value => {
            return !!value.checked;
          });

          const checkedValue = checkedIndex !== -1 ? filter.menu.items[checkedIndex].data : false;
          out[filter.fieldName] = checkedValue;

          break;

        case 'Ext.Builder.ListFilterDate':
          const dateFormat = 'YYYY/MM/DD';

          const begin = filter.fieldValue[0] ? moment(filter.fieldValue[0], dateFormat) : '';
          const end = filter.fieldValue[1] ? moment(filter.fieldValue[1], dateFormat) : '';

          out[filter.fieldName] = [begin, end];

          break;

        default:
          break;
      }
    });

    return out;
  };

  /**
   * Построит массив кнопок для табличной строки
   */
  function buildButtonsForTableRow(text, record){

    // Кнопки в строке
    const rowButtons = moduleData.getIn(['params', 'rowButtons']);

    // Если есть дочерние записи, значит это группирующая строка для которой кнопки не нужны
    if (record.children !== undefined){
      return false;
    }

    return rowButtons.toJS().map(rowButtonVal => {

      // { tooltip: "Удалить", iconCls: "icon-delete", action: "delete", state: "delete", actionText: "" }
      // {state: "add_obj", customBtnName: "ParamsAddObjBtn", customLayer: ""}
      const configButton = rowButtonVal;

      const subList = moduleData.get('subLibs').toJS();

      let button;

      // Это кастомная кнопка?
      if ( configButton.customBtnName !== undefined && configButton.customLayer !== undefined ){

        const customButtonData = {
          record,
          mainContainerData: moduleData,
          dispatch,
          configButton
        };

        switch (configButton.customBtnName) {
          case 'ParamsAddObjBtn':
            button = <ParamsAddObjBtn {...customButtonData} />;
            break;

          case 'ParamsEditBtn':
            button = <ParamsEditBtn {...customButtonData} />;
            break;

          case 'ParamsDelBtn':
            button = <ParamsDelBtn {...customButtonData} />;
            break;

          case 'ApproveBtn':

            switch (getModuleNameByCustomBtnName(subList, 'ApproveBtn')){
              case 'Review':
                button = <ReviewApproveBtn {...customButtonData} />;
                break;

              default:
                button = <ApproveBtn {...customButtonData} />;
            }

            break;

          case 'DeleteOrCloneBtn':
            button = <DeleteOrCloneBtn {...customButtonData} />;
            break;

          case 'DelBtn':
            button = <DelBtn {...customButtonData} />;
            break;

          case 'OverrideBtn':
            button = <OverrideBtn {...customButtonData} />;
            break;

          case 'EditBtn':
            button = <EditBtn {...customButtonData} />;
            break;

          case 'StatusGroupBtn':

            switch ( getModuleNameByCustomBtnName(subList, 'StatusGroupBtn') ){
              case 'Auth':
                button = <AuthStatusGroupBtn {...customButtonData} />;
                break;

              default:
                button = <StatusGroupBtn {...customButtonData} />;
            }

            break;

          case 'TranslateBtn':
            button = <TranslateBtn {...customButtonData} />;
            break;

          case 'RejectBtn':
            button = <ReviewRejectBtn {...customButtonData} />;
            break;

          case 'ParamsCleanObjBtn':
            button = <RegionsParamsCleanObjBtn {...customButtonData} />;
            break;

          case 'ViewModificationsBtn':
            button = <CatalogViewModificationsBtn {...customButtonData} />;
            break;

          case 'DelFieldBtn':
            button = <CardEditorDelFieldBtn {...customButtonData} />;
            break;

          case 'EditFieldBtn':
            button = <CardEditorEditFieldBtn {...customButtonData} />;
            break;

          default:
            button = <ParamsAddObjBtn {...customButtonData} />;
          // throw new Exception('unknown custom button');
        }

      } else {
        // Стандартная кнопка

        button = (
          <ButtonInTableRow
            // key={index}
            configButton={configButton}
            handleClickOnButton={handleClickOnButtonRow(configButton, record)}
          />
        );

      } // else


      return button;

    });

  };

  const isEditing = (record, columnName) => {
    return `${record.key}_${columnName}` == editingKey
  };

  const initColumns = () => {

    let outColumns = [];

    // Колонки
    const columnsModel = moduleData.getIn(['params', 'columnsModel']);

    // Кнопки в строке
    const rowButtons = moduleData.getIn(['params', 'rowButtons']);

    // сумма flex колонок
    const sumFlex = columnsModel.reduce((sum, val) => {
      const curVal = val.get('flex') || 0;
      return sum + curVal;
    }, 0);

    const container = document.querySelector(".sk-tabs");

    // общая ширина контейнера для вывода колонок
    const sumWidth = container ? container.clientWidth - 210 - 41 : 782;

    // ширина колонки под кнопки
    const buttonsWidth = rowButtons.size * 18 + 18;
    const buttonsWidthPercent = Math.round(100 * buttonsWidth / sumWidth);

    // считаем остаток ширины для flex колонок (убираем все с фиксированной
    // шириной и колонку с кнопками)
    let restWidth = sumWidth - buttonsWidth;
    restWidth = columnsModel.reduce((rest, val) => {
      const width = val.get('width') || 0;
      if (width) {
        return rest - width;
      }
      return rest;
    }, restWidth);

    outColumns = columnsModel.map(value => {
      const flex = value.get('flex') || 0;

      // % от ширины поля
      let width;

      const baseWidth = value.get('width') || 0;
      if (baseWidth) {
        width = Math.round(100 * baseWidth / sumWidth);
      } else {
        width = Math.round(100 * restWidth * flex / sumFlex / sumWidth);
      }

      let column = {
        title: value.get('text'),
        dataIndex: value.get('dataIndex'),
        align: value.get('jsView') === 'check' ? 'center' : 'left', // todo
        width: `${width}%`,
        render: (text, record) => {

          const highlightingListItem = moduleData.getIn(['params', 'init', 'highlighting_list_item'], List()).toJS();

          let style = {width: `${width}%`};
          let hint = '';

          if ( highlightingListItem !== undefined ){

            for (const key in highlightingListItem){

              const {hint: highlightHint, condition, style: highlightStyle} = highlightingListItem[key];

              // Преобразовать условия к виду строки с разделителями | для поиска в нескольких значениях
              const sCondition = '|' + condition.split(',').join('|') + '|';

              // Проверить условие и пропустить, если не подходит
              if (sCondition.indexOf('|' + String(record[key]) + '|') < 0)
                continue;

              let highlightStyleObject = {};
              _.compact(highlightStyle.split(';')).forEach((item) => {
                const [key, value] = item.split(':');
                highlightStyleObject[_.trim(key)] = _.trim(value);
              });

              style = {...style, ...highlightStyleObject};

              if (highlightHint){
                hint = highlightHint;
              }
            }

          }

          const mainComponent = <div title={text} style={style} dangerouslySetInnerHTML={{ __html: getColumnValue(record, value) }} />;

          return (
            hint ? (
              <Tooltip title={hint}>
                {mainComponent}
              </Tooltip>
            ) : mainComponent
          )
        }
      };

      if (value.get('sortable')) {
        column = {
          ...column,
          sorter: (a, b) => {
            const fieldSort = value.get('dataIndex');

            const typeField = value.get('jsView');

            let res;
            switch (typeField) {
              case 'num':
              case 'money':
              case 'float':
                res = a[fieldSort] - b[fieldSort];
                break;

              default:
                res = a[fieldSort] > b[fieldSort] ? 1 : -1;
            }

            // сравнение строк
            return res;
          },
        };
      }


      if (value.get('listSaveCmd')) {
        // console.log(value.get('jsView'))
        column = {
          ...column,
          onCell: (record, rowIndex) => ({
            onClick: () => {
              // если это не группирующая строка и не чекбокс
              if ( (record.children === undefined) && (value.get('jsView') !== 'check') ){
                setEditingKey(`${record.key}_${value.get('dataIndex')}`);
                setEditingRow(rowIndex);
              }
            },
            record,
            listSaveCmd: value.get('listSaveCmd'),
            jsView: value.get('jsView'),
            // editable: true,
            editing: isEditing(record, value.get('dataIndex')),
            setEditingKey: setEditingKey,
            dataIndex: value.get('dataIndex'),
            title: value.get('text'),
            handleSave: handleSave,
            width: `${width}%`,
          }),
        };
      }

      return column;
    });

    // Добавляем кнопки в строку
    outColumns = outColumns.push({
      title: '',
      dataIndex: '__buttonsInRow__',
      width: `${buttonsWidthPercent}%`,
      align: 'right',
      render: buildButtonsForTableRow,
    });

    outColumns = outColumns.map((col) => ({
      ...col,
      onHeaderCell: column => {
        return {
          width: column.width,
          // onResize: this.handleResize(index),
        };
      },
    }));

    return outColumns.toJS();
  };

  const initComponents = () => {

    const indexFirstEditableCell = moduleData.getIn(['params', 'columnsModel']).findIndex( item => Boolean(item.get('listSaveCmd')) );

    // Таблица редактируемая?
    const isEditableTable = indexFirstEditableCell !== -1;

    // Drag&Drop?
    const isDraggable = Boolean(moduleData.getIn(['params', 'ddAction']));

    const components = {};

    let row;
    let cell;

    if (isEditableTable && isDraggable) {
      row = Form.create()(EditableDecorator(DragableBodyRow));
      cell = EditableCell;
    } else if ( isEditableTable && !isDraggable ){
      row = Form.create()(EditableRow);
      cell = EditableCell;
    } else if ( !isEditableTable && isDraggable ){
      row = DragableBodyRow;
    } else {
      row = TableRow;
    }

    if (cell) {
      components.body = {
        ...components.body,
        cell,
      };
    }

    if (row) {
      components.body = {
        ...components.body,
        row,
      };
    }

    return components;
  };

  const initDataSource = () => {

    // Данные
    const data = moduleData.getIn(['params', 'items']);

    // поле группировки записей
    const groupField = moduleData.getIn(['params', 'init', 'groupslist_groupField']) || '';

    if (groupField) {
      const recordsIndexedByGroup = _.groupBy(data.toJS(), groupField);

      const dataRecords = [];
      const titleGroup = moduleData.getIn(['params', 'columnsModel', 0, 'dataIndex']);

      Object.entries(recordsIndexedByGroup).forEach(([nameGroup, records]) => {

        const children = records.map(value => {
          return {
            ...value,
            key: uniqueId(),
          };
        });

        const groupWithChildRecords = {
          key: nameGroup,
          [titleGroup]: nameGroup,
          children,
        };

        dataRecords.push(groupWithChildRecords);
      });

      return dataRecords;
    }

    return data.toJS().map(value => {
      return {
        ...value,
        key: uniqueId() //value.id,
      };
    });
  };

  const onSelectChange = (selRowKeys, selRows) => {
    setSelectedRowKeys(selRowKeys);
    setSelectedRows(selRows);
  };

  const initCheckBoxSelection = () => {

    let config = {};

    if (moduleData.getIn(['params', 'checkboxSelection'])) {

      const rowSelection = {
        selectedRowKeys,
        onChange: onSelectChange,
      };

      config = {
        rowSelection,
      };
    }

    return config;
  };

  const initPagination = () => {

    const total = moduleData.getIn(['params', 'itemsTotal']);
    const onPage = moduleData.getIn(['params', 'itemsOnPage']) || 0;
    const pageNum = (moduleData.getIn(['params', 'pageNum']) || 0) + 1;

    let config = {
      pagination: false,
    };

    if (onPage) {
      config = {
        pagination: {
          onChange: page => {

            dispatch({
              type: 'skGlobal/handleFilterData',
              payload: {
                path: moduleData.get('path'),
                page: page-1,
                values: {
                  cmd: moduleData.getIn(['params', 'actionNameLoad']),
                  ...filterValues, // из стейта
                },
              },
            });
          },
          showQuickJumper: true,
          total,
          showTotal: (totalCount, range) => sk.dict('totalPaginationCount')
            .replace('{range0}', range[0])
            .replace('{range1}', range[1])
            .replace('{totalCount}', totalCount),
          pageSize: onPage,
          defaultCurrent: pageNum,
        },
      };
    } // end if

    return config;
  };

  /**
   * Обработка клика по кнопке в табличной строке
   */
  const handleClickOnButtonRow = (configButton, tableRecord) => () => {

    // { tooltip: "Редактировать", iconCls: "icon-edit", action: "show", state: "edit_form" }
    const {action, state, actionText=''} = configButton;

    // Если action не указан, то ничего не делаем
    if ( !action ){
      return null;
    }

    const {key, ...data} = tableRecord;

    const data4Sending = {
      path: moduleData.get('path'),
      cmd: action,
      data: data,
    };

    const postData = () => {
      dispatch({
        type: 'skGlobal/handleClickOnButtonTable',
        payload: data4Sending,
      });
    };

    switch (state) {

      // удалить
      case 'delete':

        let rowText = tableRecord.title || tableRecord.name;

        if (!rowText){
          let titleField;

          const tableColumns = moduleData.getIn(['params', 'columnsModel']).toJS();

          for (const index in tableColumns){
            const curColumn = tableColumns[index];
            if ( curColumn && !curColumn.hidden && curColumn.dataIndex ){
              titleField = curColumn.dataIndex;
              break;
            }
          }

          if ( titleField ){
            rowText = tableRecord[titleField];
          }

        }

        const header = sk.dict('delRowHeader');
        const text = sk.dict('delRow').replace('{0}', rowText);

        sk.showModal(<div dangerouslySetInnerHTML={{ __html: header }} />, <div dangerouslySetInnerHTML={{ __html: text }} />, () => {
          postData();
        });

        break;

      // Действия, требующие подтверждения выполнения операции
      case 'allow_do':

        sk.showModal(<div dangerouslySetInnerHTML={{ __html: sk.dict('allowDoHeader') }} />, <div dangerouslySetInnerHTML={{ __html: actionText }} />, () => {
          postData();
        });

        break;

      default:
        postData();
    }

  };

  const selectRow = record => {
    const aSelectedRowKeys = [...selectedRowKeys];
    const aSelectedRows = [...selectedRows];
    // вложенные условия для aSelectedRows сделаны чтоб
    // состояния двух selectedRowKeys и selectedRows были одинаковы
    if (aSelectedRowKeys.indexOf(record.key) >= 0) {
      aSelectedRowKeys.splice(aSelectedRowKeys.indexOf(record.key), 1);
      if (aSelectedRows.indexOf(record) >= 0) {
        aSelectedRows.splice(aSelectedRows.indexOf(record), 1);
      }
    } else {
      aSelectedRowKeys.push(record.key);
      if (aSelectedRows.indexOf(record) === -1) {
        aSelectedRows.push(record);
      }
    }
    setSelectedRowKeys(aSelectedRowKeys);
    setSelectedRows(aSelectedRows);
  };

  const onClickByRow = (record, rowIndex) => event => {
    selectRow(record);
  };


  const onDoubleClickByRow = (record, rowIndex) => event => {

    // Если есть дочерние записи, значит это группирующая строка
    if (record.children !== undefined){
      return false;
    }
    // console.log('onDoubleClick', record, rowIndex, event);

    // Кнопки в строке
    const rowButtons = moduleData.getIn(['params', 'rowButtons']).toJS();

    // console.log('rowButtons', rowButtons);

    // найти кнопку редактирования
    const indexButton = rowButtons.findIndex(value => {
      return value.state === 'edit_form';
    });

    if (indexButton !== -1) {
      const targetButton = rowButtons[indexButton];

      // Это кастомная кнопка?
      if ( targetButton.customBtnName !== undefined ){

        const subList = moduleData.get('subLibs').toJS();
        const modulePath = buildCustomBtnModulePathByName(subList, targetButton.customBtnName);

        // Вызываем её обработчик
        import(`./${modulePath}`)
          .then(module => {
            module.handler({
              record,
              mainContainerData: moduleData,
              dispatch,
              configButton: targetButton
            });
          })
          .catch(err => {
            console.log('err', err);
            // main.textContent = err.message;
          });

      } else {
        // стандартная кнопка

        // console.log('standard button', targetButton.action, targetButton.state, record);
        handleClickOnButtonRow(targetButton, record)();

      }

      setEditingKey('');

    }
  };

  function swap(arr, a, b) {
    const dragEl = arr[a];

    arr.splice(a, 1);
    arr.splice(b, 0, dragEl);

    return arr;
  }

  const moveRow = (record) => (dragIndex, hoverIndex, dragRowKey, hoverRowKey) => {

    let data = [...dataSource];
    let dragRow = {};
    let dragHover = {};

    const {group, groupTitle} = record;

    if (group !== undefined || groupTitle !== undefined) {
      dataSource.forEach(function(item, i, array) {
        if (item.key === group || item.key === groupTitle) {
          dragRow = item.children[dragIndex];
          dragHover = item.children[hoverIndex];
          data[i].children = swap(item.children, dragIndex, hoverIndex);
        }
      });
    } else {
      dragRow = data[dragIndex];
      dragHover = data[hoverIndex];
      data = swap(data, dragIndex, hoverIndex);
    }

    const direction = dragIndex < hoverIndex ? 'after' : 'before';

    if (dragRow === undefined || dragHover === undefined) {
      sk.showMessages([[sk.dict('error'), moduleData.getIn(['init', 'dict', 'notParameterDragRowOrDragHover'])]], 'error');

      return;
    }

    if (
      (group !== undefined || groupTitle !== undefined)
      && (
        dragRow.entity !== dragHover.entity || dragRow.group !== dragHover.group
        || Number(dragRow.key) !== Number(dragRowKey) || Number(dragHover.key) !== Number(hoverRowKey
        )
      )
    ) {
      sk.showMessages([[sk.dict('error'), moduleData.getIn(['init', 'dict', 'DraggableFieldsMustBeFromTheSameGroup'])]], 'error');
      return;
    }

    if (dragRow.children !== undefined || dragHover.children !== undefined ) {
      sk.showMessages([[sk.dict('error'), moduleData.getIn(['init', 'dict', 'GroupDragAndDropNotAllowed'])]], 'error');
      return;
    }

    dispatch({
      type: 'skGlobal/sortTableItems',
      payload: {
        path: moduleData.get('path'),
        dragItem: dragRow,
        hoverItem: dragHover,
        cmdCommand: moduleData.getIn(['params', 'ddAction']),
        direction
      }
    });

    setDataSource(
      update(dataSource, { $set: data })
    );

  };

  const initOnRowEvents = () => {
    return {
      onRow: (record, rowIndex) => {
        return {
          index: rowIndex,
          moveRow: moveRow(record),
          setEditingKey,
          onClick: onClickByRow(record, rowIndex),
          onDoubleClick: onDoubleClickByRow(record, rowIndex),
          isDraggable: () => {
            // если в строке таблицы редактируется колонка, то запретить перетаскивание
            return !(editingRow !== null && editingRow === rowIndex)
          }
        };
      },
    };
  };

  const changeFilterForm = form => () => {

    const formValues = form.getFieldsValue();

    setFilterValues(formValues);

    dispatch({
      type: 'skGlobal/handleFilterData',
      payload: {
        path: moduleData.get('path'),
        page: 0,
        values: {
          cmd: moduleData.getIn(['params', 'actionNameLoad']),
          ...formValues,
        },
      },
    });
  };

  useEffect(() => {
    const tmpColumns = initColumns();
    const tmpDataSource = initDataSource();
    const tmpComponents = initComponents();
    const tmpFilterValues = initFilterValues();

    const expandedRowKeys = tmpDataSource.filter(item => item.children !== undefined).map(({key}) => String(key));
    setExpandedRowKeys(expandedRowKeys);

    setColumns(tmpColumns);
    setDataSource(tmpDataSource);
    setComponents(tmpComponents);
    setFilterValues(tmpFilterValues);

  }, [moduleData]);

  useEffect(() => {
    const tmpColumns = initColumns();
    setColumns(tmpColumns)
    if (!editingKey) {
      setEditingRow(null);
    }
  }, [editingKey]);

  const config = {
    components,
    columns,
    dataSource,
    rowClassName: (record, index) => {
      return record.children ? 'expanded-row' : 'editable-row'
    },
    bordered: true,
    loading: saveEffect,
    expandedRowKeys: expandedRowKeys,
    onExpand: (expanded, record) => {

      if ( expanded ){
        setExpandedRowKeys([
          ...expandedRowKeys,
          String(record.key)
        ]);
      } else {
        setExpandedRowKeys(
          difference(expandedRowKeys, [String(record.key)])
        );
      }

    },
    // scroll={{y: true}}
    size: 'small',
    indentSize: 0,
    defaultExpandAllRows: true,
    ...initCheckBoxSelection(),
    ...initPagination(),
    ...initOnRowEvents(),
  };

  const content = (
    <Fragment>
      {Object.entries(filterValues).length === 0 && filterValues.constructor === Object ? (
        null
      ) : (
        <FilterForm tabKey={tabKey} changeFilterForm={changeFilterForm} />
      )}
      <Table rowKey="key" {...config} />
    </Fragment>
  );

  return (
    <TestPanel
      title={moduleData.getIn(['params', 'panelTitle'])}
      buttonsData={moduleData.getIn(['params', 'dockedItems', 'left'])}
      handleClickOnButton={handleClickOnButtonInLeftPanel(selectedRows, moduleData, dispatch)}
      selectedRows={selectedRows}
    >
      {content}
    </TestPanel>
  );
};

const ConnectedTable = connect(({ skGlobal, loading }, { tabKey }) => {
  return {
    moduleData: skGlobal.tabs.get(tabKey),
    saveEffect:
      !!loading.effects['skGlobal/handleFilterButton'] ||
      !!loading.effects['skGlobal/handleFilterData']
  };
})(TableComponent);

export default DragDropContext(HTML5Backend)(ConnectedTable);
