import React, {useState, useEffect} from 'react';
import { Collapse } from 'antd';
// noinspection ES6UnusedImports
import style from './style.less';
/**
 * Формат записи массива groups
 * collapsible: boolean,
 * collapsed: boolean,
 * groupTitle: string,
 * items: array
 * */
export default ({ groups }) => {
  let groupsArr = {...groups};
  let panels = [];

  // по группам строим панели
  Object.entries(groupsArr).forEach(([key,{collapsible, groupTitle, items}]) => {
      const newPanel = (
        <Collapse.Panel
          key={key}
          showArrow={!!collapsible}
          header={groupTitle}
        >
          {items}
        </Collapse.Panel>
      );

      panels = [...panels, newPanel];
  });

  // по группам заполняем массив ключей
  const getActiveKeys = () => {
    let activeKeys = [];
    Object.entries(groups).forEach(([key, {collapsed}]) => {
      if (!collapsed) {
        activeKeys = [...activeKeys, key];
      }
    });

    return activeKeys;
  };

  // открытие закрытие панели
  const onChange = (value) => {
    setActiveGroupKeys(value)
  };

  const activeKeys = getActiveKeys();
  const [activeGroupKeys, setActiveGroupKeys] = useState(activeKeys);

  // если группы обновились, меняем массив ключей
  useEffect(() => {
    setActiveGroupKeys(getActiveKeys())
  }, [Object.entries(groups).map(group => group[1].groupTitle).join('-')]);

  return (
    <Collapse
      defaultActiveKey={activeGroupKeys}
      activeKey={activeGroupKeys}
      className="sk-content-collapse"
      onChange={onChange}
    >
      {panels}
    </Collapse>
  );

}
