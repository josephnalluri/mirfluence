% Algo 3 : Ranking finder for Cumulative union
% input: n number of .txt file (n> 1)
% output: result (1 .txt file)

%  To run:
%  filenames = {'a.txt', 'b.txt'} ;          
%  Ranking_finder(filenames) ;

function Ranking_finder(filenames)
    Z1=load(filenames{1}); % load 1st file
    Z1(:,3)=Z1(:,2)/length(Z1);
    for k = 2 : length(filenames) % combine 1 file to a single file
        Z2=load(filenames{k});
        Z2(:,3)=Z2(:,2)/length(Z2);
        Z1=union(Z1,Z2,'rows');
    end
    R_30=zeros(520,1); % add the infunence of one node for different disease in the catgories
    for i=1:520
        R_30(i)=sum(Z1(find(Z1(:,1)==i),3));
    end
    R=[1:1:520]'; % add node number
    R_30=horzcat(R,R_30);
    R_30=sortrows(R_30,-2);
    R_30 = R_30(1:10,:);
    fileID=fopen('CSV/files/RankFinderResult.txt','w'); % save result to a .txt file
    fprintf(fileID,'%d %f\n',R_30');
    fclose(fileID);
    clear all
end
