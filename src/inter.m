% function needed for intersection function

function [Z]=inter(X1,X2);
% http://www.mathworks.com/help/matlab/ref/intersect.html#btcnv0p-11
[C,iX1,iX2]=intersect(X1(:,1:2),X2(:,1:2),'rows');
c=X1(iX1,3).*X2(iX2,3);
Z=[C,c];
