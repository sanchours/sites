import React from 'react';
import { Tooltip } from 'antd'
import _ from 'lodash'
import Icon from "../../../../Icon/Index";


export const handler = (props) => {

  const {record, mainContainerData, dispatch} = props;

  // record = {group: ".", id: "1508", key: "1508", name: "layout", parent: "78", show_val: "", title: "", value: ""}

  dispatch({
    type: 'skGlobal/handleCustomButtonInParameters',
    payload: {
      dispatchCmd: 'handleEditObjBtn',
      path: mainContainerData.get('path'),
      data: _.merge(
        {
          cmd: 'edit',
          data: {
            id: record.id
          }
        },
        mainContainerData.getIn(['params', 'serviceData']).toJS()
      )
    }
  });

};


const handleClick = (props) => (e) => {
  // останавливаем дальнейшее всплытие события
  e.stopPropagation();

  handler(props);

};

export default (props) => {

  const {record, mainContainerData} = props;

  const sectionId = parseInt(mainContainerData.getIn(['params', 'serviceData', 'sectionId']));

  let tooltipText;
  let iconAlias;
  
  if (parseInt(record.parent) === sectionId){
    // редактировать
    tooltipText = mainContainerData.getIn(['params', 'init', 'lang', 'upd']);
    iconAlias = 'icon-edit';
  } else {
    // Исправить для раздела
    tooltipText = mainContainerData.getIn(['params', 'init', 'lang', 'paramAddForSection']);
    iconAlias = 'icon-add';
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
