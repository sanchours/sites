import React from 'react';
import {Tooltip} from "antd";
import _ from 'lodash'
import * as sk from '../../../../../../services/_sk/api'
import Icon from "../../../../Icon/Index";

const handleClick = (props) => (e) => {
  // останавливаем дальнейшее всплытие события
  e.stopPropagation();

  const {record, mainContainerData, dispatch} = props;

  // record = {group: ".", id: "1508", key: "1508", name: "layout", parent: "78", show_val: "", title: "", value: ""}

  const sectionId = parseInt(mainContainerData.getIn(['params', 'serviceData', 'sectionId'], ''));

  if (parseInt(record.parent) === sectionId) {
    // удалить
    const rowText = record.title || record.name;

    const text = sk.dict('delRow').replace('{0}', rowText);

    sk.showModal(<div dangerouslySetInnerHTML={{ __html: sk.dict('delRowHeader') }} />,<div dangerouslySetInnerHTML={{ __html: text }} />, () => {

      dispatch({
        type: 'skGlobal/handleCustomButtonInParameters',
        payload: {
          dispatchCmd: 'handleDelObjBtn',
          path: mainContainerData.get('path'),
          data: _.merge(
            {
              cmd: 'delete',
              data: {
                id: record.id
              }
            },
            mainContainerData.getIn(['params', 'serviceData']).toJS()
          )
        }
      });

    });

  } else {

    // Дублировать для раздела
    dispatch({
      type: 'skGlobal/handleCustomButtonInParameters',
      payload: {
        dispatchCmd: 'handleCloneObjBtn',
        path: mainContainerData.get('path'),
        data: _.merge(
          {
            cmd: 'clone',
            data: {
              id: record.id
            }
          },
          mainContainerData.getIn(['params', 'serviceData']).toJS()
        )
      }
    });

  }

};

export default (props) => {
  const {record, mainContainerData} = props;

  const sectionId = parseInt(mainContainerData.getIn(['params', 'serviceData', 'sectionId'], ''));

  let tooltipText;
  let iconAlias;

  if (parseInt(record.parent) === sectionId){
    // редактировать
    tooltipText = mainContainerData.getIn(['params', 'init', 'lang', 'del']);
    iconAlias = 'icon-delete';
  } else {
    // Исправить для раздела
    tooltipText = mainContainerData.getIn(['params', 'init', 'lang', 'paramCopyToSection']);
    iconAlias = 'icon-connect';
  }

  return (
    <Tooltip
      placement="bottomRight"
      title={tooltipText}
    >
      <span>
        <Icon
          alias={iconAlias}
          addProps={{onClick: handleClick(props)}}
        />
      </span>
    </Tooltip>
  );
};
